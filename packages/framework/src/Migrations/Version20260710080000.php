<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260710080000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $emailTransportCount = (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM transports WHERE type = :type AND deleted = FALSE',
            ['type' => TransportTypeEnum::TYPE_EMAIL],
        );

        if ($emailTransportCount > 0) {
            return;
        }

        $this->sql(
            'INSERT INTO transports (hidden, deleted, position, uuid, days_until_delivery, type)
                VALUES (FALSE, FALSE, (SELECT COALESCE(MAX(position), -1) + 1 FROM transports t), uuid_generate_v4(), 0, :type)',
            ['type' => TransportTypeEnum::TYPE_EMAIL],
        );
        $transportId = (int)$this->connection->lastInsertId();

        $descriptionsByLocale = [
            'en' => 'Electronic vouchers will be sent to your email automatically after the order is paid. Transport does not apply to them.',
            'cs' => 'Elektronické poukazy vám odešleme automaticky na e-mail po zaplacení objednávky. Nevztahuje se na ně doprava.',
            'sk' => 'Elektronické poukazy vám odošleme automaticky na e-mail po zaplatení objednávky. Nevzťahuje sa na ne doprava.',
        ];

        foreach ($this->getAllLocales() as $locale) {
            $this->sql(
                'INSERT INTO transport_translations (translatable_id, name, description, locale)
                    VALUES (:transportId, :name, :description, :locale)',
                [
                    'transportId' => $transportId,
                    'name' => t('Email', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
                    'description' => $descriptionsByLocale[$locale] ?? $descriptionsByLocale['en'],
                    'locale' => $locale,
                ],
            );
        }

        $domainIdsWithCreatedData = $this->getDomainIdsWithCreatedData();

        foreach ($this->getAllDomainIds() as $domainId) {
            $vatId = $this->connection->fetchOne(
                'SELECT id FROM vats WHERE domain_id = :domainId ORDER BY percent ASC, id ASC LIMIT 1',
                ['domainId' => $domainId],
            );

            if ($vatId === false) {
                continue;
            }

            $this->sql(
                'INSERT INTO transport_domains (transport_id, domain_id, enabled, vat_id)
                    VALUES (:transportId, :domainId, TRUE, :vatId)',
                [
                    'transportId' => $transportId,
                    'domainId' => $domainId,
                    'vatId' => $vatId,
                ],
            );

            if (!in_array($domainId, $domainIdsWithCreatedData, true)) {
                continue;
            }

            $this->sql(
                'INSERT INTO transport_prices (transport_id, price, domain_id, max_weight)
                    VALUES (:transportId, 0, :domainId, NULL)',
                [
                    'transportId' => $transportId,
                    'domainId' => $domainId,
                ],
            );
        }
    }

    /**
     * @return int[]
     */
    private function getDomainIdsWithCreatedData(): array
    {
        $domainIds = $this->connection->fetchFirstColumn(
            'SELECT domain_id FROM setting_values WHERE name = :domainDataCreatedSettingName',
            ['domainDataCreatedSettingName' => Setting::DOMAIN_DATA_CREATED],
        );

        return array_map(intval(...), $domainIds);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180603135341 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $orderStatusesCount = $this->sqlQuery('SELECT count(*) FROM order_statuses')->fetchOne();

        if ($orderStatusesCount > 0) {
            return;
        }

        $this->createOrderStatus(1, 1);
        $this->createOrderStatus(2, 2);
        $this->createOrderStatus(3, 3);
        $this->createOrderStatus(4, 4);


        foreach ($this->getAllLocales() as $locale) {
            $this->createOrderStatusTranslations(1, t('New [adjective]', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale), $locale);
            $this->createOrderStatusTranslations(2, t('In Progress', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale), $locale);
            $this->createOrderStatusTranslations(3, t('Done', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale), $locale);
            $this->createOrderStatusTranslations(4, t('Canceled', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale), $locale);
        }

        $this->sql('ALTER SEQUENCE order_statuses_id_seq RESTART WITH 5');
    }

    private function createOrderStatus(int $orderStatusId, int $orderStatusType): void
    {
        $this->sql('INSERT INTO order_statuses (id, type) VALUES (:id, :type)', [
            'id' => $orderStatusId,
            'type' => $orderStatusType,
        ]);
    }

    private function createOrderStatusTranslations(
        int $orderStatusId,
        string $orderStatusTranslatedName,
        string $locale,
    ): void {
        $this->sql(
            'INSERT INTO order_status_translations (translatable_id, name, locale) VALUES (:translatableId, :name, :locale)',
            [
                'translatableId' => $orderStatusId,
                'name' => $orderStatusTranslatedName,
                'locale' => $locale,
            ],
        );
    }
}

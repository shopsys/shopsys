<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260805070000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $giftVoucherPaymentCount = (int)$this->connection->fetchOne(
            'SELECT COUNT(*) FROM payments WHERE type = :type AND deleted = FALSE',
            ['type' => PaymentTypeEnum::TYPE_GIFT_VOUCHER],
        );

        if ($giftVoucherPaymentCount > 0) {
            return;
        }

        $this->sql(
            'INSERT INTO payments (hidden, deleted, position, uuid, type)
                VALUES (FALSE, FALSE, (SELECT COALESCE(MAX(position), -1) + 1 FROM payments p), uuid_generate_v4(), :type)',
            ['type' => PaymentTypeEnum::TYPE_GIFT_VOUCHER],
        );
        $paymentId = (int)$this->connection->lastInsertId();

        $this->sql(
            'INSERT INTO payments_transports (payment_id, transport_id)
                SELECT :paymentId, id FROM transports WHERE deleted = FALSE',
            ['paymentId' => $paymentId],
        );

        foreach ($this->getAllLocales() as $locale) {
            $this->sql(
                'INSERT INTO payment_translations (translatable_id, name, locale)
                    VALUES (:paymentId, :name, :locale)',
                [
                    'paymentId' => $paymentId,
                    'name' => t('Voucher', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
                    'locale' => $locale,
                ],
            );
        }

        foreach ($this->getAllDomainIds() as $domainId) {
            $vatId = $this->connection->fetchOne(
                'SELECT id FROM vats WHERE domain_id = :domainId ORDER BY percent ASC, id ASC LIMIT 1',
                ['domainId' => $domainId],
            );

            if ($vatId === false) {
                continue;
            }

            $this->sql(
                'INSERT INTO payment_domains (payment_id, domain_id, enabled, vat_id, hidden_by_go_pay, order_rounding_type)
                    VALUES (:paymentId, :domainId, TRUE, :vatId, FALSE, :orderRoundingType)',
                [
                    'paymentId' => $paymentId,
                    'domainId' => $domainId,
                    'vatId' => $vatId,
                    'orderRoundingType' => OrderRoundingTypeEnum::NONE,
                ],
            );

            $this->sql(
                'INSERT INTO payment_prices (payment_id, price, domain_id)
                    VALUES (:paymentId, 0, :domainId)',
                [
                    'paymentId' => $paymentId,
                    'domainId' => $domainId,
                ],
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260708150000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transport_prices ADD currency_id INT DEFAULT NULL');
        $this->sql('DROP INDEX unique_weight_limit_on_domain');
        $this->sql('ALTER TABLE payment_prices ADD currency_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE payment_prices DROP CONSTRAINT payment_prices_pkey');
        $this->sql('ALTER TABLE promo_code_limit ADD currency_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE promo_code_limit DROP CONSTRAINT promo_code_limit_pkey');
        $this->sql('
            CREATE TABLE free_transport_and_payment_price_limits (
                domain_id INT NOT NULL,
                price NUMERIC(20, 6) NOT NULL,
                currency_id INT NOT NULL,
                PRIMARY KEY (domain_id, currency_id)
            )');
        $this->sql('CREATE INDEX IDX_F52EE79438248176 ON free_transport_and_payment_price_limits (currency_id)');
        $this->sql('ALTER TABLE free_transport_and_payment_price_limits ADD CONSTRAINT FK_F52EE79438248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE');

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            $this->backfillDefaultCurrency($domainConfig);
        }

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            foreach ($domainConfig->getCurrencyCodes() as $currencyCode) {
                if ($currencyCode === $domainConfig->getDefaultCurrencyCode()) {
                    continue;
                }

                $this->bootstrapConvertedPricesForCurrency($domainConfig, $currencyCode);
            }
        }

        $this->sql('ALTER TABLE transport_prices ALTER currency_id SET NOT NULL');
        $this->sql('ALTER TABLE transport_prices ADD CONSTRAINT FK_573018D038248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('CREATE INDEX IDX_573018D038248176 ON transport_prices (currency_id)');
        $this->sql('CREATE UNIQUE INDEX unique_weight_limit_on_domain ON transport_prices (max_weight, domain_id, transport_id, currency_id)');

        $this->sql('ALTER TABLE payment_prices ALTER currency_id SET NOT NULL');
        $this->sql('ALTER TABLE payment_prices ADD PRIMARY KEY (payment_id, domain_id, currency_id)');
        $this->sql('ALTER TABLE payment_prices ADD CONSTRAINT FK_C1F3F6CF38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('CREATE INDEX IDX_C1F3F6CF38248176 ON payment_prices (currency_id)');

        $this->sql('ALTER TABLE promo_code_limit ALTER currency_id SET NOT NULL');
        $this->sql('ALTER TABLE promo_code_limit ADD PRIMARY KEY (promo_code_id, from_price, currency_id)');
        $this->sql('ALTER TABLE promo_code_limit ADD CONSTRAINT FK_CF58514F38248176 FOREIGN KEY (currency_id) REFERENCES currencies (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->sql('CREATE INDEX IDX_CF58514F38248176 ON promo_code_limit (currency_id)');

        $this->sql("DELETE FROM setting_values WHERE name = 'freeTransportAndPaymentPriceLimit'");
    }

    private function backfillDefaultCurrency(DomainConfig $domainConfig): void
    {
        $parameters = [
            'currencyCode' => $domainConfig->getDefaultCurrencyCode(),
            'domainId' => $domainConfig->getId(),
        ];

        $this->sql(
            'UPDATE transport_prices SET currency_id = (SELECT id FROM currencies WHERE code = :currencyCode)
            WHERE domain_id = :domainId',
            $parameters,
        );

        $this->sql(
            'UPDATE payment_prices SET currency_id = (SELECT id FROM currencies WHERE code = :currencyCode)
            WHERE domain_id = :domainId',
            $parameters,
        );

        $this->sql(
            'UPDATE promo_code_limit pcl SET currency_id = (SELECT id FROM currencies WHERE code = :currencyCode)
            FROM promo_codes pc
            WHERE pcl.promo_code_id = pc.id AND pc.domain_id = :domainId',
            $parameters,
        );

        $this->sql(
            "INSERT INTO free_transport_and_payment_price_limits (domain_id, currency_id, price)
            SELECT sv.domain_id, (SELECT id FROM currencies WHERE code = :currencyCode), CAST(sv.value AS numeric)
            FROM setting_values sv
            WHERE sv.name = 'freeTransportAndPaymentPriceLimit' AND sv.domain_id = :domainId AND sv.value IS NOT NULL",
            $parameters,
        );
    }

    /**
     * The prices of the additional enabled currencies are converted by exchange rate ONCE so the checkout works
     * right after the upgrade, the administrator is expected to review them (the application never converts them at runtime)
     */
    private function bootstrapConvertedPricesForCurrency(DomainConfig $domainConfig, string $currencyCode): void
    {
        $parameters = [
            'defaultCurrencyCode' => $domainConfig->getDefaultCurrencyCode(),
            'targetCurrencyCode' => $currencyCode,
            'domainId' => $domainConfig->getId(),
        ];

        $this->sql(
            'INSERT INTO transport_prices (transport_id, price, domain_id, max_weight, currency_id)
            SELECT tp.transport_id, ROUND(tp.price * c_def.exchange_rate / c_target.exchange_rate, 6), tp.domain_id, tp.max_weight, c_target.id
            FROM transport_prices tp
            JOIN currencies c_def ON c_def.code = :defaultCurrencyCode
            JOIN currencies c_target ON c_target.code = :targetCurrencyCode
            WHERE tp.domain_id = :domainId AND tp.currency_id = c_def.id',
            $parameters,
        );

        $this->sql(
            'INSERT INTO payment_prices (payment_id, price, domain_id, currency_id)
            SELECT pp.payment_id, ROUND(pp.price * c_def.exchange_rate / c_target.exchange_rate, 6), pp.domain_id, c_target.id
            FROM payment_prices pp
            JOIN currencies c_def ON c_def.code = :defaultCurrencyCode
            JOIN currencies c_target ON c_target.code = :targetCurrencyCode
            WHERE pp.domain_id = :domainId AND pp.currency_id = c_def.id',
            $parameters,
        );

        $this->sql(
            "INSERT INTO promo_code_limit (promo_code_id, from_price, discount, currency_id)
            SELECT pcl.promo_code_id,
                ROUND(pcl.from_price * c_def.exchange_rate / c_target.exchange_rate, 6),
                CASE WHEN pc.discount_type = 'nominal'
                    THEN ROUND(pcl.discount * c_def.exchange_rate / c_target.exchange_rate, 6)
                    ELSE pcl.discount
                END,
                c_target.id
            FROM promo_code_limit pcl
            JOIN promo_codes pc ON pc.id = pcl.promo_code_id
            JOIN currencies c_def ON c_def.code = :defaultCurrencyCode
            JOIN currencies c_target ON c_target.code = :targetCurrencyCode
            WHERE pc.domain_id = :domainId AND pcl.currency_id = c_def.id",
            $parameters,
        );

        $this->sql(
            'INSERT INTO free_transport_and_payment_price_limits (domain_id, currency_id, price)
            SELECT ftl.domain_id, c_target.id, ROUND(ftl.price * c_def.exchange_rate / c_target.exchange_rate, 6)
            FROM free_transport_and_payment_price_limits ftl
            JOIN currencies c_def ON c_def.code = :defaultCurrencyCode
            JOIN currencies c_target ON c_target.code = :targetCurrencyCode
            WHERE ftl.domain_id = :domainId AND ftl.currency_id = c_def.id',
            $parameters,
        );
    }
}

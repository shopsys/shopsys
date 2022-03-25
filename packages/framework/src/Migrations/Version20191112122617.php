<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191112122617 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payment_domains ADD vat_id INT NOT NULL DEFAULT 1');
        $this->sql(
            'ALTER TABLE payment_domains ADD CONSTRAINT FK_9532B177B5B63A6B FOREIGN KEY (vat_id) REFERENCES vats (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->sql('CREATE INDEX IDX_9532B177B5B63A6B ON payment_domains (vat_id)');
        $this->sql('ALTER TABLE payment_domains ALTER vat_id DROP DEFAULT');

        $this->sql('ALTER TABLE payment_prices DROP CONSTRAINT payment_prices_pkey');
        $this->sql('ALTER TABLE payment_prices ADD domain_id INT NOT NULL DEFAULT 0');
        $this->sql('ALTER TABLE payment_prices ALTER domain_id DROP DEFAULT');

        $this->sql('ALTER TABLE transport_domains ADD vat_id INT NOT NULL DEFAULT 1');
        $this->sql(
            'ALTER TABLE transport_domains ADD CONSTRAINT FK_18AC7F6CB5B63A6B FOREIGN KEY (vat_id) REFERENCES vats (id) NOT DEFERRABLE INITIALLY IMMEDIATE'
        );
        $this->sql('ALTER TABLE transport_domains ALTER vat_id DROP DEFAULT');

        $this->sql('ALTER TABLE transport_prices DROP CONSTRAINT transport_prices_pkey');
        $this->sql('ALTER TABLE transport_prices ADD domain_id INT NOT NULL DEFAULT 0');
        $this->sql('ALTER TABLE transport_prices ALTER domain_id DROP DEFAULT');

        $this->migratePaymentsDomains();
        $this->migratePaymentsPrices();
        $this->migrateTransportDomains();
        $this->migrateTransportPrices();

        $this->sql('ALTER TABLE payment_prices ADD PRIMARY KEY (payment_id, domain_id)');
        $this->sql('ALTER TABLE transport_prices ADD PRIMARY KEY (transport_id, domain_id)');

        $this->sql('ALTER TABLE payments DROP vat_id');
        $this->sql('ALTER TABLE transports DROP vat_id');

        $this->sql('ALTER TABLE payment_prices DROP currency_id');
        $this->sql('ALTER TABLE transport_prices DROP currency_id');

        $this->sql('ALTER TABLE vats DROP tmp_original_id;');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    private function migratePaymentsDomains(): void
    {
        $payments = $this->sql('SELECT id, vat_id FROM payments')->fetchAllAssociative();

        foreach ($payments as $payment) {
            $paymentDomains = $this->sql(
                'SELECT id, domain_id FROM payment_domains where payment_id = :paymentId',
                ['paymentId' => $payment['id']]
            )->fetchAllAssociative();

            foreach ($paymentDomains as $paymentDomain) {
                $vatId = $payment['vat_id'];
                if ($paymentDomain['domain_id'] > 1) {
                    $vatId = $this
                        ->sql(
                            'SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId',
                            [
                                'tmpOriginalId' => $payment['vat_id'],
                                'domainId' => $paymentDomain['domain_id'],
                            ]
                        )
                        ->fetchOne();
                }

                $this->sql('UPDATE payment_domains SET vat_id = :vatId WHERE id = :id', [
                    'vatId' => $vatId,
                    'id' => $paymentDomain['id'],
                ]);
            }
        }
    }

    private function migratePaymentsPrices(): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $priceCreated = [];

            $defaultCurrencyForDomain = $this->sql(
                'SELECT value from setting_values where name = \'defaultDomainCurrencyId\' AND domain_id = :domainId',
                ['domainId' => $domainId]
            )->fetchOne();

            $paymentPricesByCurrency = $this->sql(
                'SELECT payment_id, price, domain_id FROM payment_prices WHERE currency_id = :currencyId',
                [
                    'currencyId' => $defaultCurrencyForDomain,
                ]
            )->fetchAllAssociative();

            foreach ($paymentPricesByCurrency as $paymentPrice) {
                if ($paymentPrice['domain_id'] === 0) {
                    $this->sql(
                        'UPDATE payment_prices set domain_id = :domainId WHERE payment_id = :paymentId and currency_id = :currencyId',
                        [
                            'domainId' => $domainId,
                            'paymentId' => $paymentPrice['payment_id'],
                            'currencyId' => $defaultCurrencyForDomain,
                        ]
                    );
                } else {
                    if (array_key_exists($paymentPrice['payment_id'], $priceCreated) === true) {
                        continue;
                    }

                    $this->sql(
                        'INSERT INTO payment_prices (payment_id, price, domain_id, currency_id) VALUES (:paymentId, :price, :domainId, :currencyId)',
                        [
                            'paymentId' => $paymentPrice['payment_id'],
                            'price' => $paymentPrice['price'],
                            'domainId' => $domainId,
                            'currencyId' => $defaultCurrencyForDomain,
                        ]
                    );
                    $priceCreated[$paymentPrice['payment_id']] = true;
                }
            }
        }
    }

    private function migrateTransportDomains(): void
    {
        $transports = $this->sql('SELECT id, vat_id FROM transports')->fetchAllAssociative();

        foreach ($transports as $transport) {
            $transportDomains = $this->sql(
                'SELECT id, domain_id FROM transport_domains where transport_id = :transportId',
                ['transportId' => $transport['id']]
            )->fetchAllAssociative();

            foreach ($transportDomains as $transportDomain) {
                $vatId = $transport['vat_id'];
                if ($transportDomain['domain_id'] > 1) {
                    $vatId = $this
                        ->sql(
                            'SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId',
                            [
                                'tmpOriginalId' => $transport['vat_id'],
                                'domainId' => $transportDomain['domain_id'],
                            ]
                        )
                        ->fetchOne();
                }

                $this->sql('UPDATE transport_domains SET vat_id = :vatId WHERE id = :id', [
                    'vatId' => $vatId,
                    'id' => $transportDomain['id'],
                ]);
            }
        }
    }

    private function migrateTransportPrices(): void
    {
        foreach ($this->getAllDomainIds() as $domainId) {
            $priceCreated = [];

            $defaultCurrencyForDomain = $this->sql(
                'SELECT value from setting_values where name = \'defaultDomainCurrencyId\' AND domain_id = :domainId',
                ['domainId' => $domainId]
            )->fetchOne();

            $transportPricesByCurrency = $this->sql(
                'SELECT transport_id, price, domain_id FROM transport_prices WHERE currency_id = :currencyId',
                [
                    'currencyId' => $defaultCurrencyForDomain,
                ]
            )->fetchAllAssociative();

            foreach ($transportPricesByCurrency as $transportPrice) {
                if ($transportPrice['domain_id'] === 0) {
                    $this->sql(
                        'UPDATE transport_prices set domain_id = :domainId WHERE transport_id = :transportId and currency_id = :currencyId',
                        [
                            'domainId' => $domainId,
                            'transportId' => $transportPrice['transport_id'],
                            'currencyId' => $defaultCurrencyForDomain,
                        ]
                    );
                } else {
                    if (array_key_exists($transportPrice['transport_id'], $priceCreated) === true) {
                        continue;
                    }

                    $this->sql(
                        'INSERT INTO transport_prices (transport_id, price, domain_id, currency_id) VALUES (:transportId, :price, :domainId, :currencyId)',
                        [
                            'transportId' => $transportPrice['transport_id'],
                            'price' => $transportPrice['price'],
                            'domainId' => $domainId,
                            'currencyId' => $defaultCurrencyForDomain,
                        ]
                    );

                    $priceCreated[$transportPrice['transport_id']] = true;
                }
            }
        }
    }
}

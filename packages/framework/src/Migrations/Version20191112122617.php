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
        $this->sql('ALTER TABLE payment_prices DROP CONSTRAINT payment_prices_pkey');
        $this->sql('ALTER TABLE payment_prices ADD domain_id INT NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE payment_prices ADD vat_id INT NOT NULL DEFAULT 1');
        $this->sql('
            ALTER TABLE
                payment_prices
            ADD
                CONSTRAINT FK_C1F3F6CFB5B63A6B FOREIGN KEY (vat_id) REFERENCES vats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_C1F3F6CFB5B63A6B ON payment_prices (vat_id)');
        $this->sql('ALTER TABLE payment_prices ADD PRIMARY KEY (payment_id, currency_id, domain_id)');
        $this->sql('ALTER TABLE payment_prices ALTER vat_id DROP DEFAULT');
        $this->sql('ALTER TABLE payment_prices ALTER domain_id DROP DEFAULT');

        $this->sql('ALTER TABLE transport_prices DROP CONSTRAINT transport_prices_pkey');
        $this->sql('ALTER TABLE transport_prices ADD domain_id INT NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE transport_prices ADD vat_id INT NOT NULL DEFAULT 1');
        $this->sql('
            ALTER TABLE
                transport_prices
            ADD
                CONSTRAINT FK_573018D0B5B63A6B FOREIGN KEY (vat_id) REFERENCES vats (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_573018D0B5B63A6B ON transport_prices (vat_id)');
        $this->sql('ALTER TABLE transport_prices ADD PRIMARY KEY (transport_id, currency_id, domain_id)');

        $this->sql('ALTER TABLE transport_prices ALTER vat_id DROP DEFAULT');
        $this->sql('ALTER TABLE transport_prices ALTER domain_id DROP DEFAULT');

        $this->migratePaymentPrice();
        $this->migrateTransportPrice();

        $this->sql('ALTER TABLE transports DROP vat_id');
        $this->sql('ALTER TABLE payments DROP vat_id');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    private function migratePaymentPrice(): void
    {
        $allPaymentsPrices = $this->sql('SELECT payment_id, currency_id, price, vat_id, domain_id FROM payment_prices')->fetchAll();

        foreach ($this->getAllDomainIds() as $domainId) {
            foreach ($allPaymentsPrices as $paymentPrice) {
                $oldVatId = $this->sql('SELECT vat_id from payments where id = :paymentId', ['paymentId' => $paymentPrice['payment_id']])->fetchColumn(0);

                if ($domainId === 1) {
                    $this->sql('UPDATE payment_prices SET vat_id = :newVatId WHERE payment_id = :paymentId AND currency_id = :currencyId and domain_id = :domainId', [
                        'newVatId' => $oldVatId,
                        'paymentId' => $paymentPrice['payment_id'],
                        'currencyId' => $paymentPrice['currency_id'],
                        'domainId' => $paymentPrice['domain_id'],
                    ]);
                } else {
                    $newVatId = $this
                        ->sql('SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId', [
                            'tmpOriginalId' => $oldVatId,
                            'domainId' => $domainId,
                        ])
                        ->fetchColumn(0);

                    if ($newVatId !== false) {
                        $this->sql('INSERT INTO payment_prices (payment_id, currency_id, price, vat_id, domain_id) VALUES (:paymentId, :currencyId, :price, :vatId, :domainId)', [
                            'paymentId' => $paymentPrice['payment_id'],
                            'currencyId' => $paymentPrice['currency_id'],
                            'price' => $paymentPrice['price'],
                            'vatId' => $newVatId,
                            'domainId' => $domainId,
                        ]);
                    }
                }
            }
        }
    }

    private function migrateTransportPrice(): void
    {
        $allTransportsPrices = $this->sql('SELECT transport_id, currency_id, price, vat_id, domain_id FROM transport_prices')->fetchAll();

        foreach ($this->getAllDomainIds() as $domainId) {
            foreach ($allTransportsPrices as $transportPrice) {
                $oldVatId = $this->sql('SELECT vat_id from transports where id = :transportId', ['transportId' => $transportPrice['transport_id']])->fetchColumn(0);

                if ($domainId === 1) {
                    $this->sql('UPDATE transport_prices SET vat_id = :newVatId WHERE transport_id = :transportId AND currency_id = :currencyId and domain_id = :domainId', [
                        'newVatId' => $oldVatId,
                        'transportId' => $transportPrice['transport_id'],
                        'currencyId' => $transportPrice['currency_id'],
                        'domainId' => $transportPrice['domain_id'],
                    ]);
                } else {
                    $newVatId = $this
                        ->sql('SELECT id FROM vats where tmp_original_id = :tmpOriginalId and domain_id = :domainId', [
                            'tmpOriginalId' => $oldVatId,
                            'domainId' => $domainId,
                        ])
                        ->fetchColumn(0);

                    if ($newVatId !== false) {
                        $this->sql('INSERT INTO transport_prices (transport_id, currency_id, price, vat_id, domain_id) VALUES (:transportId, :currencyId, :price, :vatId, :domainId)', [
                            'transportId' => $transportPrice['transport_id'],
                            'currencyId' => $transportPrice['currency_id'],
                            'price' => $transportPrice['price'],
                            'vatId' => $newVatId,
                            'domainId' => $domainId,
                        ]);
                    }
                }
            }
        }
    }
}

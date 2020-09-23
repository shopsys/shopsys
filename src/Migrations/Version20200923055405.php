<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Migrations\MultidomainMigrationTrait;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200923055405 extends AbstractMigration
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE transport_pallet_prices (
                id SERIAL NOT NULL,
                transport_id INT DEFAULT NULL,
                domain_id INT NOT NULL,
                products_price_to NUMERIC(20, 6) DEFAULT NULL,
                transport_price NUMERIC(20, 6) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_5F5A472E9909C13F ON transport_pallet_prices (transport_id)');
        $this->sql('COMMENT ON COLUMN transport_pallet_prices.products_price_to IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN transport_pallet_prices.transport_price IS \'(DC2Type:money)\'');
        $this->sql('
            ALTER TABLE transport_pallet_prices
                ADD CONSTRAINT FK_5F5A472E9909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        foreach ($this->getAllDomainIds() as $domainId) {
            $this->sql(
                'INSERT INTO transport_pallet_prices (transport_id, domain_id, transport_price) 
                    SELECT t.id, :domainId, 0.0 FROM transports t WHERE t.type = \'pallet\'',
                [
                    'domainId' => $domainId,
                ]
            );
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

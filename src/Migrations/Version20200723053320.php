<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200723053320 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD can_be_shipped_as_package BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE product_domains ALTER can_be_shipped_as_package DROP DEFAULT;');
        $this->sql('ALTER TABLE transports ADD type VARCHAR(255) NOT NULL DEFAULT \'common\'');
        $this->sql('ALTER TABLE transports ALTER type DROP DEFAULT;');
        $this->sql('CREATE TABLE transport_packages (
                id SERIAL NOT NULL, 
                transport_id INT NOT NULL, 
                domain_id INT NOT NULL, 
                max_product_packages_count INT DEFAULT NULL, 
                max_weight INT NOT NULL, 
                price_with_vat NUMERIC(20, 6) NOT NULL, 
                max_girth INT DEFAULT NULL, 
                dimension1 INT DEFAULT NULL, 
                dimension2 INT DEFAULT NULL, 
                dimension3 INT DEFAULT NULL, 
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_519BD5ED9909C13F ON transport_packages (transport_id);');
        $this->sql('COMMENT ON COLUMN transport_packages.price_with_vat IS \'(DC2Type:money)\'');
        $this->sql('ALTER TABLE transport_packages ADD CONSTRAINT FK_519BD5ED9909C13F FOREIGN KEY (transport_id) 
            REFERENCES transports (id) NOT DEFERRABLE INITIALLY IMMEDIATE;');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

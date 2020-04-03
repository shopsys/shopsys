<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200403122227 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_type_domains (
                id SERIAL NOT NULL,
                product_type_id INT NOT NULL,
                domain_id INT NOT NULL,
                free_transport_minimal_price NUMERIC(20, 6) DEFAULT NULL,
                free_transport BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('COMMENT ON COLUMN product_type_domains.free_transport_minimal_price IS \'(DC2Type:money)\'');

        $this->sql('CREATE INDEX IDX_29FEF20314959723 ON product_type_domains (product_type_id)');
        $this->sql('CREATE UNIQUE INDEX product_type_domain ON product_type_domains (product_type_id, domain_id)');
        $this->sql('
            ALTER TABLE
                product_type_domains
            ADD
                CONSTRAINT FK_29FEF20314959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

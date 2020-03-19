<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200306172413 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD product_type_id INT DEFAULT NULL');
        $this->sql('UPDATE product_domains 
            SET product_type_id = (SELECT p.product_type_id FROM products p WHERE p.id = product_id)');
        $this->sql('ALTER TABLE order_items ALTER product_type_id DROP DEFAULT');
        $this->sql('ALTER TABLE product_domains ALTER product_type_id SET NOT NULL');
        $this->sql('ALTER TABLE product_domains
            ADD CONSTRAINT FK_5DA2A42D14959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) 
                NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_5DA2A42D14959723 ON product_domains (product_type_id)');

        $this->sql('DROP INDEX idx_b3ba5a5a14959723');
        $this->sql('ALTER TABLE products DROP product_type_id');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

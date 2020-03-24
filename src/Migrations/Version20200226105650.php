<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200226105650 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE transport_product_type (
                transport_id INT NOT NULL, 
                product_type_id INT NOT NULL, 
                PRIMARY KEY(transport_id, product_type_id)
            )');
        $this->sql('CREATE INDEX IDX_9FC48EC89909C13F ON transport_product_type (transport_id)');
        $this->sql('CREATE INDEX IDX_9FC48EC814959723 ON transport_product_type (product_type_id)');
        $this->sql('ALTER TABLE transport_product_type 
            ADD CONSTRAINT FK_9FC48EC89909C13F FOREIGN KEY (transport_id) REFERENCES transports (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE transport_product_type 
            ADD CONSTRAINT FK_9FC48EC814959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) 
                ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Set relation for all transports to all product types -> all can use all by default
        $this->sql('INSERT INTO transport_product_type 
            (transport_id, product_type_id) 
            (SELECT t.id, pt.id FROM transports t, product_types pt)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

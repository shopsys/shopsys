<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210711163712 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE product_stores (product_id INT NOT NULL, store_id INT NOT NULL, product_exposed BOOLEAN NOT NULL, PRIMARY KEY(product_id, store_id))');
        $this->sql('CREATE INDEX IDX_B7EC3D684584665A ON product_stores (product_id)');
        $this->sql('CREATE INDEX IDX_B7EC3D68B092A811 ON product_stores (store_id)');
        $this->sql('ALTER TABLE product_stores ADD CONSTRAINT FK_B7EC3D684584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE product_stores ADD CONSTRAINT FK_B7EC3D68B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('ALTER TABLE product_stocks DROP product_exposed');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

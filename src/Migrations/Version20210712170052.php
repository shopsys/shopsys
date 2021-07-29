<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210712170052 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_items DROP CONSTRAINT FK_62809DB078AE585C');
        $this->sql('DROP INDEX IDX_62809DB078AE585C');
        $this->sql('ALTER TABLE order_items RENAME COLUMN personal_pickup_stock_id TO personal_pickup_store_id');
        $this->sql('
            ALTER TABLE
                order_items
            ADD
                CONSTRAINT FK_62809DB0C5F1915D FOREIGN KEY (personal_pickup_store_id) REFERENCES stores (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_62809DB0C5F1915D ON order_items (personal_pickup_store_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

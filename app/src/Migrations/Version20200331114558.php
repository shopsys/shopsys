<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200331114558 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_items ADD personal_pickup_stock_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                order_items
            ADD
                CONSTRAINT FK_62809DB078AE585C FOREIGN KEY (personal_pickup_stock_id) REFERENCES stocks (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_62809DB078AE585C ON order_items (personal_pickup_stock_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200624103557 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_items ADD related_order_item_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                order_items
            ADD
                CONSTRAINT FK_62809DB0C47318A FOREIGN KEY (related_order_item_id) REFERENCES order_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE UNIQUE INDEX UNIQ_62809DB0C47318A ON order_items (related_order_item_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

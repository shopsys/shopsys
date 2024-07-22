<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240617204710 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE order_item_relations (
                order_item_id INT NOT NULL,
                related_item_id INT NOT NULL,
                PRIMARY KEY(order_item_id, related_item_id)
            )');
        $this->sql('CREATE INDEX IDX_70C67938E415FB15 ON order_item_relations (order_item_id)');
        $this->sql('CREATE INDEX IDX_70C679382D7698FB ON order_item_relations (related_item_id)');
        $this->sql('
            ALTER TABLE
                order_item_relations
            ADD
                CONSTRAINT FK_70C67938E415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                order_item_relations
            ADD
                CONSTRAINT FK_70C679382D7698FB FOREIGN KEY (related_item_id) REFERENCES order_items (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        if (!$this->isAppMigrationNotInstalledRemoveIfExists('Version20200624103557')) {
            $this->sql('
                INSERT INTO
                    order_item_relations (order_item_id, related_item_id)
                SELECT
                    oi.id AS order_item_id,
                    oi.related_order_item_id AS related_item_id
                FROM
                    order_items oi
                WHERE
                    oi.related_order_item_id IS NOT NULL');

            $this->sql('ALTER TABLE order_items DROP related_order_item_id');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

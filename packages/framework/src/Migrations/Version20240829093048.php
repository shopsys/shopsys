<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240829093048 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaint_items DROP CONSTRAINT FK_A0C592FBE415FB15');
        $this->sql('ALTER TABLE complaint_items ADD product_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE complaint_items ALTER order_item_id DROP NOT NULL');
        $this->sql('
            ALTER TABLE
                complaint_items
            ADD
                CONSTRAINT FK_A0C592FB4584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                complaint_items
            ADD
                CONSTRAINT FK_A0C592FBE415FB15 FOREIGN KEY (order_item_id) REFERENCES order_items (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_A0C592FB4584665A ON complaint_items (product_id)');
        $this->sql('ALTER TABLE complaint_items ADD product_name VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE complaint_items ADD catnum VARCHAR(255) DEFAULT NULL');
        $this->sql('UPDATE complaint_items 
                        SET product_id = order_items.product_id,
                            product_name = order_items.name,
                            catnum = order_items.catnum
                        FROM order_items 
                        WHERE complaint_items.order_item_id = order_items.id');
        $this->sql('ALTER TABLE complaint_items ALTER product_name SET NOT NULL');
        $this->sql('ALTER TABLE complaint_items ALTER catnum SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

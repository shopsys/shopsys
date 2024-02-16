<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240102112523 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD total_product_price_without_vat NUMERIC(20, 6) NOT NULL DEFAULT 0');
        $this->sql('ALTER TABLE orders ALTER total_product_price_without_vat DROP DEFAULT');
        $this->sql('COMMENT ON COLUMN orders.total_product_price_without_vat IS \'(DC2Type:money)\'');
        $this->sql('UPDATE orders
            SET total_product_price_without_vat = (
                SELECT COALESCE(SUM(order_items.price_with_vat * order_items.quantity), 0)
                FROM order_items
                WHERE order_items.order_id = orders.id
                  AND order_items.type = \'product\'
        )');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

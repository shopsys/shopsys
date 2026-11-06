<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261106100000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ADD gift_main_product_id INT DEFAULT NULL');
        $this->sql("CREATE UNIQUE INDEX unique_cart_product_gift_pair ON cart_items (cart_id, product_id, gift_main_product_id) WHERE (type = 'productGift')");
        $this->sql('CREATE INDEX IDX_CART_ITEM_GIFT_MAIN_PRODUCT ON cart_items (gift_main_product_id)');
        $this->sql('ALTER TABLE cart_items ADD CONSTRAINT FK_CART_ITEM_GIFT_MAIN_PRODUCT FOREIGN KEY (gift_main_product_id) REFERENCES products (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE carts RENAME COLUMN removed_gift_product_ids TO removed_product_gifts');
        // An old removal applied to every qualifying product already in that cart.
        $this->sql(<<<'SQL'
            UPDATE carts c SET removed_product_gifts = COALESCE((
                SELECT json_agg(DISTINCT ci.product_id::text || ':' || gift_id)
                FROM cart_items ci
                CROSS JOIN LATERAL json_array_elements_text(c.removed_product_gifts) gift_id
                WHERE ci.cart_id = c.id AND ci.type = 'product' AND ci.product_id IS NOT NULL
            ), '[]'::json)
            SQL);
    }
}

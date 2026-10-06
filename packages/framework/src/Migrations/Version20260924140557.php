<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260924140557 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items RENAME COLUMN watched_price TO watched_price_with_vat');
        $this->sql('ALTER TABLE carts RENAME COLUMN transport_watched_price TO transport_watched_price_with_vat');
        $this->sql('ALTER TABLE carts RENAME COLUMN payment_watched_price TO payment_watched_price_with_vat');
        $this->sql('ALTER TABLE cart_items ADD watched_price_without_vat NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD transport_watched_price_without_vat NUMERIC(20, 6) DEFAULT NULL');
        $this->sql('ALTER TABLE carts ADD payment_watched_price_without_vat NUMERIC(20, 6) DEFAULT NULL');

        // null watched prices, so they're not reported as changed due to added without vat price
        $this->sql('UPDATE cart_items SET watched_price_with_vat = NULL');
        $this->sql('UPDATE carts SET transport_watched_price_with_vat = NULL WHERE transport_id IS NOT NULL');
        $this->sql('UPDATE carts SET payment_watched_price_with_vat = NULL WHERE payment_id IS NOT NULL');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260310120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD currency_code VARCHAR(3) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE orders ADD currency_rounding_type VARCHAR(15) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE orders ADD currency_rounding_places_price_without_vat INT NOT NULL DEFAULT 2');
        $this->sql('ALTER TABLE orders ADD currency_min_fraction_digits INT NOT NULL DEFAULT 2');
        $this->sql('ALTER TABLE orders ADD payment_czk_rounding BOOLEAN NOT NULL DEFAULT FALSE');

        $this->sql('
            UPDATE orders o
            SET
                currency_code = c.code,
                currency_rounding_type = c.rounding_type,
                currency_rounding_places_price_without_vat = c.rounding_places_price_without_vat,
                currency_min_fraction_digits = c.min_fraction_digits,
                payment_czk_rounding = COALESCE(
                    (SELECT p.czk_rounding
                     FROM order_items oi
                     JOIN payments p ON p.id = oi.payment_id
                     WHERE oi.order_id = o.id AND oi.type = \'payment\'
                     LIMIT 1),
                    FALSE
                )
            FROM currencies c
            WHERE c.id = o.currency_id
        ');

        $this->sql('ALTER TABLE orders ALTER COLUMN currency_code DROP DEFAULT');
        $this->sql('ALTER TABLE orders ALTER COLUMN currency_rounding_type DROP DEFAULT');
        $this->sql('ALTER TABLE orders ALTER COLUMN currency_rounding_places_price_without_vat DROP DEFAULT');
        $this->sql('ALTER TABLE orders ALTER COLUMN currency_min_fraction_digits DROP DEFAULT');
        $this->sql('ALTER TABLE orders ALTER COLUMN payment_czk_rounding DROP DEFAULT');

        $this->sql('ALTER TABLE orders DROP CONSTRAINT IF EXISTS fk_e52ffdee38248176');
        $this->sql('DROP INDEX IF EXISTS idx_e52ffdee38248176');
        $this->sql('ALTER TABLE orders DROP COLUMN currency_id');

        $this->sql('ALTER TABLE currencies ADD CONSTRAINT UNIQ_37C4469377153098 UNIQUE (code)');
        $this->sql('CREATE INDEX idx_orders_currency_code ON orders (currency_code)');
        $this->sql('ALTER TABLE orders ADD CONSTRAINT fk_orders_currency_code FOREIGN KEY (currency_code) REFERENCES currencies (code)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20190215092226 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('COMMENT ON COLUMN cart_items.watched_price IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN order_items.price_without_vat IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN order_items.price_with_vat IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN orders.total_price_with_vat IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN orders.total_price_without_vat IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN orders.total_product_price_with_vat IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN payment_prices.price IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN product_calculated_prices.price_with_vat IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN product_manual_input_prices.input_price IS \'(DC2Type:money)\'');
        $this->sql('COMMENT ON COLUMN transport_prices.price IS \'(DC2Type:money)\'');
    }
}

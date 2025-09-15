<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250915110500 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ALTER unit_price_without_vat_at_addition TYPE NUMERIC(20,6)');
        $this->sql('ALTER TABLE cart_items ALTER unit_price_with_vat_at_addition TYPE NUMERIC(20,6)');
        $this->sql("COMMENT ON COLUMN cart_items.unit_price_without_vat_at_addition IS '(DC2Type:money)'");
        $this->sql("COMMENT ON COLUMN cart_items.unit_price_with_vat_at_addition IS '(DC2Type:money)'");
    }
}


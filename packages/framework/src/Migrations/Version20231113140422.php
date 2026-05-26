<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20231113140422 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products DROP COLUMN recalculate_price');
        $this->sql('DROP TRIGGER IF EXISTS recalc_main_variant_price ON product_visibilities');
        $this->sql('DROP FUNCTION IF EXISTS set_main_variant_price_recalculation_by_product_visibility');
        $this->sql('DROP TABLE product_calculated_prices');
    }
}

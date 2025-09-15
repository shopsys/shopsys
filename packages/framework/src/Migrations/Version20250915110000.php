<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250915110000 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ADD COLUMN unit_price_without_vat_at_addition NUMERIC(20,6) DEFAULT NULL');
        $this->sql('ALTER TABLE cart_items ADD COLUMN unit_price_with_vat_at_addition NUMERIC(20,6) DEFAULT NULL');
    }
}

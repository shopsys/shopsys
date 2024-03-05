<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240206145944 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM setting_values WHERE name = \'defaultAvailabilityInStockId\'');
        $this->sql('DELETE FROM enabled_modules WHERE name = \'productStockCalculations\'');
        $this->dropColumnIfExists('out_of_stock_action', 'products');
        $this->dropColumnIfExists('out_of_stock_availability_id', 'products');
        $this->dropColumnIfExists('stock_quantity', 'products');
        $this->dropColumnIfExists('using_stock', 'products');
        $this->dropColumnIfExists('availability_id', 'products');
        $this->sql('DROP TABLE IF EXISTS availability_translations');
        $this->sql('DROP TABLE IF EXISTS availabilities');
    }

    /**
     * @param string $columnName
     * @param string $tableName
     */
    private function dropColumnIfExists(string $columnName, string $tableName): void
    {
        $this->sql(sprintf('ALTER TABLE %s DROP COLUMN IF EXISTS %s', $tableName, $columnName));
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

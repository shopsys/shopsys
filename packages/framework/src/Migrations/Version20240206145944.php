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
        $this->dropColumn('products', 'out_of_stock_action');
        $this->dropColumn('products', 'out_of_stock_availability_id');
        $this->dropColumn('products', 'stock_quantity');
        $this->dropColumn('products', 'using_stock');
        $this->dropColumn('products', 'availability_id');
        $this->sql('DROP TABLE IF EXISTS availabilities');
        $this->sql('DROP TABLE IF EXISTS availability_translations');
    }

    /**
     * @param string $columnName
     * @param string $tableName
     */
    private function dropColumn(string $columnName, string $tableName): void
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

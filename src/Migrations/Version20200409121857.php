<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200409121857 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE products SET using_stock = false');

        $inStockAvailabilityId = $this->sql('SELECT value FROM setting_values WHERE name = :defaultAvailability', ['defaultAvailability' => Setting::DEFAULT_AVAILABILITY_IN_STOCK])->fetchColumn(0);

        $this->sql('UPDATE products SET availability_id = :inStockAvailabilityId', ['inStockAvailabilityId' => $inStockAvailabilityId]);
        $this->sql('DELETE FROM availabilities WHERE id != :inStockAvailabilityId', ['inStockAvailabilityId' => $inStockAvailabilityId]);
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

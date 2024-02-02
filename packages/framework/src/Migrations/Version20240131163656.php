<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240131163656 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE store_opening_hours ADD opening_time VARCHAR(5) DEFAULT NULL');
        $this->sql('ALTER TABLE store_opening_hours ADD closing_time VARCHAR(5) DEFAULT NULL');

        $openingHoursData = $this->sql('SELECT store_id, day_of_week, first_opening_time, first_closing_time, second_opening_time, second_closing_time FROM store_opening_hours')->fetchAllAssociative();

        foreach ($openingHoursData as $openingHoursRow) {
            $firstOpeningTime = $openingHoursRow['first_opening_time'];
            $firstClosingTime = $openingHoursRow['first_closing_time'];
            $secondOpeningTime = $openingHoursRow['second_opening_time'];
            $secondClosingTime = $openingHoursRow['second_closing_time'];
            $storeId = $openingHoursRow['store_id'];
            $dayOfWeek = $openingHoursRow['day_of_week'];

            if ($firstOpeningTime !== null && $firstClosingTime !== null) {
                $this->insertIntoOpeningHours($firstOpeningTime, $firstClosingTime, $storeId, $dayOfWeek);
            }

            if ($secondOpeningTime !== null && $secondClosingTime !== null) {
                $this->insertIntoOpeningHours($secondOpeningTime, $secondClosingTime, $storeId, $dayOfWeek);
            }
        }
        $this->sql('DELETE FROM store_opening_hours WHERE opening_time IS NULL AND closing_time IS NULL');
        $this->sql('ALTER TABLE store_opening_hours DROP COLUMN first_opening_time, DROP COLUMN first_closing_time, DROP COLUMN second_opening_time, DROP COLUMN second_closing_time');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }

    /**
     * @param string $openingTime
     * @param string $closingTime
     * @param int $storeId
     * @param int $dayOfWeek
     */
    private function insertIntoOpeningHours(
        string $openingTime,
        string $closingTime,
        int $storeId,
        int $dayOfWeek,
    ): void {
        $this->sql('INSERT INTO store_opening_hours (opening_time, closing_time, store_id, day_of_week) VALUES (:openingTime, :closingTime, :storeId, :dayOfWeek)', [
            'openingTime' => $openingTime,
            'closingTime' => $closingTime,
            'storeId' => $storeId,
            'dayOfWeek' => $dayOfWeek,
        ]);
    }
}

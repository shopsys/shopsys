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
        $this->sql('
            CREATE TABLE store_opening_hours_ranges (
                id SERIAL NOT NULL,
                opening_hours_id INT NOT NULL,
                opening_time VARCHAR(5) NOT NULL,
                closing_time VARCHAR(5) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_D9B14247CE298D68 ON store_opening_hours_ranges (opening_hours_id)');
        $this->sql('
            ALTER TABLE
                store_opening_hours_ranges
            ADD
                CONSTRAINT FK_D9B14247CE298D68 FOREIGN KEY (opening_hours_id) REFERENCES store_opening_hours (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $openingHoursData = $this->sql('SELECT id, first_opening_time, first_closing_time, second_opening_time, second_closing_time FROM store_opening_hours')->fetchAllAssociative();

        foreach ($openingHoursData as $openingHoursRow) {
            $firstOpeningTime = $openingHoursRow['first_opening_time'];
            $firstClosingTime = $openingHoursRow['first_closing_time'];
            $secondOpeningTime = $openingHoursRow['second_opening_time'];
            $secondClosingTime = $openingHoursRow['second_closing_time'];
            $id = $openingHoursRow['id'];

            if ($firstOpeningTime !== null && $firstClosingTime !== null) {
                $this->insertIntoOpeningHoursRanges($firstOpeningTime, $firstClosingTime, $id);
            }

            if ($secondOpeningTime !== null && $secondClosingTime !== null) {
                $this->insertIntoOpeningHoursRanges($secondOpeningTime, $secondClosingTime, $id);
            }
        }
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
     * @param int $openingHoursId
     */
    private function insertIntoOpeningHoursRanges(
        string $openingTime,
        string $closingTime,
        int $openingHoursId,
    ): void {
        $this->sql('INSERT INTO store_opening_hours_ranges (opening_time, closing_time, opening_hours_id) VALUES (:openingTime, :closingTime, :openingHoursId)', [
            'openingTime' => $openingTime,
            'closingTime' => $closingTime,
            'openingHoursId' => $openingHoursId,
        ]);
    }
}

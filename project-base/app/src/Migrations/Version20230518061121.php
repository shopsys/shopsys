<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230518061121 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE store_opening_hours (
                id SERIAL NOT NULL,
                store_id INT NOT NULL,
                day_of_week INT NOT NULL,
                first_opening_time VARCHAR(5) DEFAULT NULL,
                first_closing_time VARCHAR(5) DEFAULT NULL,
                second_opening_time VARCHAR(5) DEFAULT NULL,
                second_closing_time VARCHAR(5) DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_7E35C95B092A811 ON store_opening_hours (store_id)');
        $this->sql('
            ALTER TABLE
                store_opening_hours
            ADD
                CONSTRAINT FK_7E35C95B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $storeIds = $this->sql('SELECT id FROM stores')->fetchAllAssociative();

        foreach (array_column($storeIds, 'id') as $storeId) {
            for ($i = 1; $i <= 7; $i++) {
                $this->sql(
                    'INSERT INTO store_opening_hours (store_id, day_of_week, first_opening_time, first_closing_time, second_opening_time, second_closing_time)
                    VALUES (:storeId, :dayOfWeek, null, null, null, null)',
                    [
                        'storeId' => $storeId,
                        'dayOfWeek' => $i,
                    ],
                );
            }
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230524064748 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE closed_days (
                id SERIAL NOT NULL,
                domain_id INT NOT NULL,
                date DATE NOT NULL,
                name VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('
            CREATE TABLE closed_day_excluded_stores (
                closed_day_id INT NOT NULL,
                store_id INT NOT NULL,
                PRIMARY KEY(closed_day_id, store_id)
            )');
        $this->sql('CREATE INDEX IDX_B4EC517608F9E8F ON closed_day_excluded_stores (closed_day_id)');
        $this->sql('CREATE INDEX IDX_B4EC517B092A811 ON closed_day_excluded_stores (store_id)');
        $this->sql('
            ALTER TABLE
                closed_day_excluded_stores
            ADD
                CONSTRAINT FK_B0423CD01998AAAD FOREIGN KEY (closed_day_id) REFERENCES closed_days (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                closed_day_excluded_stores
            ADD
                CONSTRAINT FK_B0423CD0B092A811 FOREIGN KEY (store_id) REFERENCES stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

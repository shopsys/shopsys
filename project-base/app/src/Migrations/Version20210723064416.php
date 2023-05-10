<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210723064416 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stores DROP address');
        $this->sql('ALTER TABLE stores ADD country_id INT NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE stores ALTER country_id DROP DEFAULT;');
        $this->sql('ALTER TABLE stores ADD street VARCHAR(100) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE stores ALTER street DROP DEFAULT;');
        $this->sql('ALTER TABLE stores ADD city VARCHAR(100) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE stores ALTER city DROP DEFAULT;');
        $this->sql('ALTER TABLE stores ADD postcode VARCHAR(30) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE stores ALTER postcode DROP DEFAULT;');
        $this->sql('
            ALTER TABLE
                stores
            ADD
                CONSTRAINT FK_D5907CCCF92F3E70 FOREIGN KEY (country_id) REFERENCES countries (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_D5907CCCF92F3E70 ON stores (country_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210520112854 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE units ADD akeneo_code VARCHAR(100) DEFAULT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_E9B07449CC7118A2 ON units (akeneo_code)');

        $this->sql('ALTER TABLE parameters ADD unit_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                parameters
            ADD
                CONSTRAINT FK_69348FEF8BD700D FOREIGN KEY (unit_id) REFERENCES units (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_69348FEF8BD700D ON parameters (unit_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

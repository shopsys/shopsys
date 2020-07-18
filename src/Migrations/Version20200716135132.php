<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200716135132 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stocks ADD extraordinary_opening_hours TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE stocks ADD contact_text1 VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE stocks ADD contact_text2 VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE stocks ADD contact_info TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE stocks ADD location_lat NUMERIC(16, 13) DEFAULT NULL');
        $this->sql('ALTER TABLE stocks ADD location_lng NUMERIC(16, 13) DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

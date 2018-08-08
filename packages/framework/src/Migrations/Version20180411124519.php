<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180411124519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE countries ADD code VARCHAR(2) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}

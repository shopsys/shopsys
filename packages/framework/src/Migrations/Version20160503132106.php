<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160503132106 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE flags ADD visible BOOLEAN NOT NULL DEFAULT TRUE;');
        $this->sql('ALTER TABLE flags ALTER visible DROP DEFAULT;');
    }

    public function down(Schema $schema)
    {
    }
}

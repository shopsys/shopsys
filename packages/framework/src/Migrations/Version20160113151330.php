<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160113151330 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $sql = 'ALTER TABLE scripts
            ADD COLUMN placement TEXT NOT NULL';
        $this->sql($sql);
    }

    public function down(Schema $schema)
    {
    }
}

<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160108183213 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $sql = 'CREATE TABLE scripts
            (id SERIAL NOT NULL, name TEXT NOT NULL, code TEXT NOT NULL, PRIMARY KEY(id));';
        $this->sql($sql);
    }

    public function down(Schema $schema)
    {
    }
}

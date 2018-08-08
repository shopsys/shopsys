<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160717105120 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE articles ADD hidden BOOLEAN NOT NULL DEFAULT FALSE;');
        $this->sql('ALTER TABLE articles ALTER hidden DROP DEFAULT;');
    }

    public function down(Schema $schema)
    {
    }
}

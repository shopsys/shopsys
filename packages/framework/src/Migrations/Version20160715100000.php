<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160715100000 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE setting_values ALTER type TYPE VARCHAR(8)');
    }

    public function down(Schema $schema)
    {
    }
}

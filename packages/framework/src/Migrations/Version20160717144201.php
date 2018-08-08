<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160717144201 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE slider_items ADD hidden BOOLEAN NOT NULL DEFAULT FALSE;');
        $this->sql('ALTER TABLE slider_items ALTER hidden DROP DEFAULT;');
    }

    public function down(Schema $schema)
    {
    }
}

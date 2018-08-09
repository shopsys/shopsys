<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160420165146 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE category_domains ADD COLUMN description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
    }
}

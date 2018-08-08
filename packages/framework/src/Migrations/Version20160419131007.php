<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160419131007 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE products ADD ordering_priority INT NOT NULL DEFAULT 0;');
        $this->sql('ALTER TABLE products ALTER ordering_priority DROP DEFAULT;');
    }

    public function down(Schema $schema)
    {
    }
}

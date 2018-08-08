<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160422164530 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE products ALTER availability_id DROP NOT NULL;');
    }

    public function down(Schema $schema)
    {
    }
}

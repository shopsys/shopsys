<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160314174500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE adverts ALTER type SET NOT NULL;');
    }

    public function down(Schema $schema)
    {
    }
}

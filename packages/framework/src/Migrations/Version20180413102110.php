<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180413102110 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('DROP TABLE plugin_data_values');
    }

    public function down(Schema $schema)
    {
    }
}

<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20151230012022 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql(
            'CREATE TABLE cron_modules (module_id VARCHAR(255) NOT NULL, scheduled BOOLEAN NOT NULL, PRIMARY KEY(module_id));'
        );
    }

    public function down(Schema $schema)
    {
    }
}

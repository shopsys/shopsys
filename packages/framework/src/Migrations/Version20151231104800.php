<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20151231104800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->sql(
            'ALTER TABLE cron_modules ADD suspended BOOLEAN NOT NULL DEFAULT FALSE;'
        );
    }

    public function down(Schema $schema): void
    {
    }
}

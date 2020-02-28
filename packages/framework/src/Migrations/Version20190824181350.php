<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20190824181350 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE cron_modules ADD enabled BOOLEAN DEFAULT \'true\' NOT NULL');
        $this->sql('ALTER TABLE cron_modules ADD status VARCHAR(255) DEFAULT \'ok\' NOT NULL');
        $this->sql('ALTER TABLE cron_modules ALTER status DROP DEFAULT');
        $this->sql('ALTER TABLE cron_modules ADD last_started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('ALTER TABLE cron_modules ADD last_finished_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('ALTER TABLE cron_modules ADD last_duration INT DEFAULT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

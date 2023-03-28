<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230313123007 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE cron_module_runs (
                id SERIAL NOT NULL,
                cron_module_id VARCHAR(255) NOT NULL,
                status VARCHAR(255) NOT NULL,
                started_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                finished_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                duration INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_63DABEAEA21F2847 ON cron_module_runs (cron_module_id)');
        $this->sql('
            ALTER TABLE
                cron_module_runs
            ADD
                CONSTRAINT FK_63DABEAEA21F2847 FOREIGN KEY (cron_module_id) REFERENCES cron_modules (service_id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

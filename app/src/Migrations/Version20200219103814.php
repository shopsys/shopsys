<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200219103814 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE transfer_issues (
                id SERIAL NOT NULL,
                transfer_id INT NOT NULL,
                severity VARCHAR(10) NOT NULL,
                message TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_BF6E22B0537048AF ON transfer_issues (transfer_id)');
        $this->sql('CREATE INDEX IDX_BF6E22B08B8E8428 ON transfer_issues (created_at)');
        $this->sql('
            CREATE TABLE tranfers (
                id SERIAL NOT NULL,
                identifier VARCHAR(100) NOT NULL,
                name VARCHAR(100) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_57310E34772E836A ON tranfers (identifier)');
        $this->sql('
            ALTER TABLE
                transfer_issues
            ADD
                CONSTRAINT FK_BF6E22B0537048AF FOREIGN KEY (transfer_id) REFERENCES tranfers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

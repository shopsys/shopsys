<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240710123712 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE uploaded_files_relations (
                id SERIAL NOT NULL,
                uploaded_file_id INT NOT NULL,
                entity_name VARCHAR(100) NOT NULL,
                entity_id INT NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_5474CBC4276973A0 ON uploaded_files_relations (uploaded_file_id)');
        $this->sql('
            ALTER TABLE
                uploaded_files_relations
            ADD
                CONSTRAINT FK_5474CBC4276973A0 FOREIGN KEY (uploaded_file_id) REFERENCES uploaded_files (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

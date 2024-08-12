<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240704105750 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE uploaded_files_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_52CCF9172C2AC5D3 ON uploaded_files_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX uploaded_files_translations_uniq_trans ON uploaded_files_translations (translatable_id, locale)');
        $this->sql('
            ALTER TABLE
                uploaded_files_translations
            ADD
                CONSTRAINT FK_52CCF9172C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES uploaded_files (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

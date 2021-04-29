<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210429125507 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE transport_type_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_11E2A9472C2AC5D3 ON transport_type_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX transport_type_translations_uniq_trans ON transport_type_translations (translatable_id, locale)');

        $this->sql('
            CREATE TABLE transport_types (
                id SERIAL NOT NULL,
                code VARCHAR(100) NOT NULL,
                PRIMARY KEY(id)
             )');
        $this->sql('
            ALTER TABLE
                transport_type_translations
            ADD
                CONSTRAINT FK_11E2A9472C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES transport_types (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE UNIQUE INDEX UNIQ_C43F2EC877153098 ON transport_types (code)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

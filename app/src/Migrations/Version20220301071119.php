<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220301071119 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE language_constant_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                translation VARCHAR(1024) NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_FCAF40BF2C2AC5D3 ON language_constant_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX language_constant_translations_uniq_trans ON language_constant_translations (translatable_id, locale)');
        $this->sql('CREATE TABLE language_constants (id SERIAL NOT NULL, key VARCHAR(1024) NOT NULL, PRIMARY KEY(id))');
        $this->sql('CREATE UNIQUE INDEX language_constants_key ON language_constants (key)');
        $this->sql('
            ALTER TABLE
                language_constant_translations
            ADD
                CONSTRAINT FK_FCAF40BF2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES language_constants (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200220124729 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE parameter_groups (
                id SERIAL NOT NULL,
                akeneo_code VARCHAR(100) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_14133381CC7118A2 ON parameter_groups (akeneo_code)');
        $this->sql('ALTER TABLE parameters ADD group_id INT DEFAULT NULL');
        $this->sql('ALTER TABLE parameters ADD akeneo_code VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE parameters ADD akeneo_type VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE parameters ADD ordering_priority INT DEFAULT 0 NOT NULL');
        $this->sql('ALTER TABLE parameters ALTER ordering_priority DROP DEFAULT');
        $this->sql('
            ALTER TABLE
                parameters
            ADD
                CONSTRAINT FK_69348FEFE54D947 FOREIGN KEY (group_id) REFERENCES parameter_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE UNIQUE INDEX UNIQ_69348FECC7118A2 ON parameters (akeneo_code)');
        $this->sql('CREATE INDEX IDX_69348FEFE54D947 ON parameters (group_id)');

        $this->sql('
            CREATE TABLE parameter_groups_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_9BF623102C2AC5D3 ON parameter_groups_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX parameter_groups_translations_uniq_trans ON parameter_groups_translations (translatable_id, locale)');
        $this->sql('
            ALTER TABLE
                parameter_groups_translations
            ADD
                CONSTRAINT FK_9BF623102C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES parameter_groups (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE parameter_groups ADD ordering_priority INT NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250120130000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220301071119')) {
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

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20220803200612')) {
            $this->sql('ALTER TABLE language_constants ALTER key TYPE TEXT');
            $this->sql('ALTER TABLE language_constant_translations ALTER translation TYPE TEXT');
        }
    }
}

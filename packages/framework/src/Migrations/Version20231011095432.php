<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231011095432 extends AbstractMigration
{
    private const WRONG_MIGRATION_VERSON = 'Shopsys\ProductFeed\GoogleBundle\Migrations\Version20230220115918';

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $isMigrationInstalledFromGoogleBundle = $this->sql(
            'SELECT COUNT(*) FROM migrations WHERE version = :version;',
            ['version' => self::WRONG_MIGRATION_VERSON],
        )->fetchOne();

        if ($isMigrationInstalledFromGoogleBundle === 0) {
            $this->sql('
                CREATE TABLE images_translations (
                    id SERIAL NOT NULL,
                    translatable_id INT NOT NULL,
                    name VARCHAR(255) DEFAULT NULL,
                    locale VARCHAR(255) NOT NULL,
                    PRIMARY KEY(id)
                )');
            $this->sql('CREATE INDEX IDX_3F73B3D02C2AC5D3 ON images_translations (translatable_id)');
            $this->sql('CREATE UNIQUE INDEX images_translations_uniq_trans ON images_translations (translatable_id, locale)');
            $this->sql('
                ALTER TABLE
                    images_translations
                ADD
                    CONSTRAINT FK_3F73B3D02C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES images (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        } else {
            $this->sql('DELETE FROM migrations WHERE version = :version;', ['version' => self::WRONG_MIGRATION_VERSON]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

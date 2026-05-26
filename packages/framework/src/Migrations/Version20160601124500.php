<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20160601124500 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE brand_translations (
            id SERIAL NOT NULL, translatable_id INT NOT NULL, 
            description TEXT DEFAULT NULL, 
            locale VARCHAR(255) NOT NULL, 
            PRIMARY KEY(id))');

        $this->sql('CREATE INDEX IDX_B018D342C2AC5D3 ON brand_translations (translatable_id)');
        $this->sql(
            'CREATE UNIQUE INDEX brand_translations_uniq_trans ON brand_translations (translatable_id, locale)',
        );

        $this->sql('ALTER TABLE brand_translations ADD CONSTRAINT FK_B018D342C2AC5D3 FOREIGN KEY (translatable_id) 
            REFERENCES brands (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

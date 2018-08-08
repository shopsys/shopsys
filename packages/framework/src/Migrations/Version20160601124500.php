<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160601124500 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('CREATE TABLE brand_translations (
            id SERIAL NOT NULL, translatable_id INT NOT NULL, 
            description TEXT DEFAULT NULL, 
            locale VARCHAR(255) NOT NULL, 
            PRIMARY KEY(id))');

        $this->sql('CREATE INDEX IDX_B018D342C2AC5D3 ON brand_translations (translatable_id)');
        $this->sql('CREATE UNIQUE INDEX brand_translations_uniq_trans ON brand_translations (translatable_id, locale)');

        $this->sql('ALTER TABLE brand_translations ADD CONSTRAINT FK_B018D342C2AC5D3 FOREIGN KEY (translatable_id) 
            REFERENCES brands (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema)
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200214104810 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_type_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_F57152D12C2AC5D3 ON product_type_translations (translatable_id)');
        $this->sql('CREATE UNIQUE INDEX product_type_translations_uniq_trans ON product_type_translations (translatable_id, locale)');
        $this->sql('CREATE TABLE product_types (id SERIAL NOT NULL, akeneo_code VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->sql('CREATE UNIQUE INDEX UNIQ_F86CF26CCC7118A2 ON product_types (akeneo_code)');
        $this->sql('
            ALTER TABLE product_type_translations
                ADD CONSTRAINT FK_F57152D12C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product_types (id) 
                    ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('INSERT INTO "product_types" ("id", "akeneo_code") VALUES
            (1,	\'common\'),
            (2,	\'oversized\')');
        $this->sql('INSERT INTO "product_type_translations" ("id", "translatable_id", "name", "locale") VALUES
            (1,	1,	\'Běžné zboží\',	\'cs\'),
            (2,	1,	\'Bežný tovar\',	\'sk\'),
            (3,	2,	\'Nadrozměrné zboží\',	\'cs\'),
            (4,	2,	\'Nadrozmený tovar\',	\'sk\')');

        $this->sql('ALTER TABLE products ADD product_type_id INT NOT NULL DEFAULT 1');
        $this->sql('ALTER TABLE products ALTER product_type_id DROP DEFAULT');
        $this->sql('ALTER TABLE products 
            ADD CONSTRAINT FK_B3BA5A5A14959723 FOREIGN KEY (product_type_id) REFERENCES product_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_B3BA5A5A14959723 ON products (product_type_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200117093808 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_series_domains (
                id SERIAL NOT NULL,
                product_series_id INT NOT NULL,
                domain_id INT NOT NULL,
                seo_title TEXT DEFAULT NULL,
                seo_meta_description TEXT DEFAULT NULL,
                seo_h1 TEXT DEFAULT NULL,
                hidden BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_8719FE433CD88711 ON product_series_domains (product_series_id)');
        $this->sql('CREATE UNIQUE INDEX product_series_domain ON product_series_domains (product_series_id, domain_id)');
        $this->sql('
            CREATE TABLE product_series_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_7B7DE2622C2AC5D3 ON product_series_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX product_series_translations_uniq_trans ON product_series_translations (translatable_id, locale)');
        $this->sql('CREATE TABLE product_series (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->sql('
            ALTER TABLE
                product_series_domains
            ADD
                CONSTRAINT FK_8719FE433CD88711 FOREIGN KEY (product_series_id) REFERENCES product_series (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_series_translations
            ADD
                CONSTRAINT FK_7B7DE2622C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product_series (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

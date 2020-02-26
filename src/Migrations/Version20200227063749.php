<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200227063749 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE product_series_category_domains (
                id SERIAL NOT NULL,
                product_series_category_id INT NOT NULL,
                domain_id INT NOT NULL,
                seo_title TEXT DEFAULT NULL,
                seo_meta_description TEXT DEFAULT NULL,
                seo_h1 TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_4BF0A34658C6412 ON product_series_category_domains (product_series_category_id)');
        $this->sql('
            CREATE UNIQUE INDEX product_series_category_domain ON product_series_category_domains (
                product_series_category_id, domain_id
            )');
        $this->sql('
            CREATE TABLE product_series_category_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_F4D0B5172C2AC5D3 ON product_series_category_translations (translatable_id)');
        $this->sql('
            CREATE UNIQUE INDEX product_series_category_translations_uniq_trans ON product_series_category_translations (translatable_id, locale)');
        $this->sql('CREATE TABLE product_series_categories (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->sql('
            CREATE TABLE product_series_product_series_category (
                product_series_id INT NOT NULL,
                product_series_category_id INT NOT NULL,
                PRIMARY KEY(
                    product_series_id, product_series_category_id
                )
            )');
        $this->sql('CREATE INDEX IDX_2A7003DA3CD88711 ON product_series_product_series_category (product_series_id)');
        $this->sql('
            CREATE INDEX IDX_2A7003DA658C6412 ON product_series_product_series_category (product_series_category_id)');
        $this->sql('
            ALTER TABLE
                product_series_category_domains
            ADD
                CONSTRAINT FK_4BF0A34658C6412 FOREIGN KEY (product_series_category_id) REFERENCES product_series_categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_series_category_translations
            ADD
                CONSTRAINT FK_F4D0B5172C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES product_series_categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_series_product_series_category
            ADD
                CONSTRAINT FK_2A7003DA3CD88711 FOREIGN KEY (product_series_id) REFERENCES product_series (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                product_series_product_series_category
            ADD
                CONSTRAINT FK_2A7003DA658C6412 FOREIGN KEY (product_series_category_id) REFERENCES product_series_categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

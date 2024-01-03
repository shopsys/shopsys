<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class Version20231212114321 extends AbstractMigration implements ContainerAwareInterface
{
    use MultidomainMigrationTrait;

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20190801103940')) {
            $this->sql('
            CREATE TABLE blog_categories (
                id SERIAL NOT NULL,
                parent_id INT DEFAULT NULL,
                level INT NOT NULL,
                lft INT NOT NULL,
                rgt INT NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_DC356481727ACA70 ON blog_categories (parent_id)');
            $this->sql('
            CREATE TABLE blog_category_domains (
                domain_id INT NOT NULL,
                blog_category_id INT NOT NULL,
                seo_title TEXT DEFAULT NULL,
                seo_meta_description TEXT DEFAULT NULL,
                seo_h1 TEXT DEFAULT NULL,
                enabled BOOLEAN NOT NULL,
                visible BOOLEAN NOT NULL,
                PRIMARY KEY(blog_category_id, domain_id)
            )');
            $this->sql('CREATE INDEX IDX_3FA0D405CB76011C ON blog_category_domains (blog_category_id)');
            $this->sql('
            CREATE TABLE blog_category_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_85D2E1FE2C2AC5D3 ON blog_category_translations (translatable_id)');
            $this->sql('
            CREATE UNIQUE INDEX blog_category_translations_uniq_trans ON blog_category_translations (translatable_id, locale)');
            $this->sql('
            CREATE TABLE blog_article_blog_category_domains (
                domain_id INT NOT NULL,
                blog_article_id INT NOT NULL,
                blog_category_id INT NOT NULL,
                PRIMARY KEY(
                    blog_article_id, blog_category_id,
                    domain_id
                )
            )');
            $this->sql('CREATE INDEX IDX_524577FC9452A475 ON blog_article_blog_category_domains (blog_article_id)');
            $this->sql('CREATE INDEX IDX_524577FCCB76011C ON blog_article_blog_category_domains (blog_category_id)');
            $this->sql('
            CREATE INDEX IDX_524577FCCB76011C115F0EE5 ON blog_article_blog_category_domains (blog_category_id, domain_id)');
            $this->sql('
            CREATE TABLE blog_article_translations (
                id SERIAL NOT NULL,
                translatable_id INT NOT NULL,
                name VARCHAR(255) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                locale VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('CREATE INDEX IDX_AEB2FC152C2AC5D3 ON blog_article_translations (translatable_id)');
            $this->sql('
            CREATE UNIQUE INDEX blog_article_translations_uniq_trans ON blog_article_translations (translatable_id, locale)');
            $this->sql('
            CREATE TABLE blog_articles (
                id SERIAL NOT NULL,
                hidden BOOLEAN NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');
            $this->sql('
            CREATE TABLE blog_article_domains (
                domain_id INT NOT NULL,
                blog_article_id INT NOT NULL,
                seo_title TEXT DEFAULT NULL,
                seo_meta_description TEXT DEFAULT NULL,
                seo_h1 TEXT DEFAULT NULL,
                visible BOOLEAN NOT NULL,
                PRIMARY KEY(blog_article_id, domain_id)
            )');
            $this->sql('CREATE INDEX IDX_D35087A99452A475 ON blog_article_domains (blog_article_id)');
            $this->sql('
            ALTER TABLE
                blog_categories
            ADD
                CONSTRAINT FK_DC356481727ACA70 FOREIGN KEY (parent_id) REFERENCES blog_categories (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_category_domains
            ADD
                CONSTRAINT FK_3FA0D405CB76011C FOREIGN KEY (blog_category_id) REFERENCES blog_categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_category_translations
            ADD
                CONSTRAINT FK_85D2E1FE2C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES blog_categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_article_blog_category_domains
            ADD
                CONSTRAINT FK_524577FC9452A475 FOREIGN KEY (blog_article_id) REFERENCES blog_articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_article_blog_category_domains
            ADD
                CONSTRAINT FK_524577FCCB76011C FOREIGN KEY (blog_category_id) REFERENCES blog_categories (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_article_translations
            ADD
                CONSTRAINT FK_AEB2FC152C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES blog_articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_article_domains
            ADD
                CONSTRAINT FK_D35087A99452A475 FOREIGN KEY (blog_article_id) REFERENCES blog_articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

            $categoriesCount = $this->sql('SELECT count(*) FROM blog_categories')->fetchOne();

            if ($categoriesCount <= 0) {
                $this->sql('INSERT INTO blog_categories (id, parent_id, level, lft, rgt) VALUES (1, null, 0, 0, 3)');
                $this->sql('INSERT INTO blog_categories (id, parent_id, level, lft, rgt) VALUES (2, 1, 1, 1, 2)');
                $this->sql('ALTER SEQUENCE blog_categories_id_seq RESTART WITH 3');

                $categoryTranslationsCreated = [];

                foreach ($this->getAllDomainIds() as $domainId) {
                    $this->sql('INSERT INTO blog_category_domains (blog_category_id, domain_id, enabled, visible) VALUES (2, ' . $domainId . ', true, true)');
                    $this->sql('INSERT INTO friendly_urls (domain_id, slug, route_name, entity_id, main) VALUES (' . $domainId . ', \'blog/\', \'front_blogcategory_detail\', 2, true)');

                    $locale = $this->getDomainLocale($domainId);

                    if (in_array($locale, $categoryTranslationsCreated, true)) {
                        continue;
                    }

                    $this->sql('INSERT INTO blog_category_translations (translatable_id, name, locale) VALUES (2, \'Hlavní stránka blogu - ' . $locale . '\', \'' . $locale . '\')');
                    $categoryTranslationsCreated[] = $locale;
                }
            }

            $this->sql('
            CREATE TABLE blog_article_products (
                blog_article_id INT NOT NULL,
                product_id INT NOT NULL,
                PRIMARY KEY(blog_article_id, product_id)
            )');
            $this->sql('CREATE INDEX IDX_925185D79452A475 ON blog_article_products (blog_article_id)');
            $this->sql('CREATE INDEX IDX_925185D74584665A ON blog_article_products (product_id)');
            $this->sql('
            ALTER TABLE
                blog_article_products
            ADD
                CONSTRAINT FK_925185D79452A475 FOREIGN KEY (blog_article_id) REFERENCES blog_articles (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
            $this->sql('
            ALTER TABLE
                blog_article_products
            ADD
                CONSTRAINT FK_925185D74584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

            $this->sql('ALTER TABLE blog_articles ADD visible_on_homepage BOOLEAN NOT NULL DEFAULT TRUE');
            $this->sql('ALTER TABLE blog_articles ALTER visible_on_homepage DROP DEFAULT');
            $this->sql('ALTER TABLE blog_articles ADD publish_date DATE NOT NULL DEFAULT \'' . date('Y-m-d') . '\'');
            $this->sql('ALTER TABLE blog_articles ALTER publish_date DROP DEFAULT');
            $this->sql('ALTER TABLE blog_article_translations ADD perex TEXT DEFAULT NULL');
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240105155555 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210709155038')) {
            $this->sql('ALTER TABLE blog_categories ADD uuid UUID DEFAULT NULL');
            $this->sql('UPDATE blog_categories SET uuid = uuid_generate_v4()');
            $this->sql('ALTER TABLE blog_categories ALTER uuid SET NOT NULL');
            $this->sql('CREATE UNIQUE INDEX UNIQ_DC356481D17F50A6 ON blog_categories (uuid)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230313113336')) {
            $this->sql('DROP TABLE blog_article_products');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20210611150732')) {
            $this->sql('ALTER TABLE blog_articles ADD uuid UUID DEFAULT NULL');
            $this->sql('UPDATE blog_articles SET uuid = uuid_generate_v4()');
            $this->sql('ALTER TABLE blog_articles ALTER uuid SET NOT NULL');
            $this->sql('CREATE UNIQUE INDEX UNIQ_CB80154FD17F50A6 ON blog_articles (uuid)');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20211001064130')) {
            $this->sql('ALTER TABLE blog_articles ALTER publish_date TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
            $this->sql('ALTER TABLE blog_articles ALTER publish_date DROP DEFAULT');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20230331101850')) {
            $this->sql('ALTER TABLE blog_articles ALTER publish_date TYPE DATE');
        }

        $this->sql('
            UPDATE blog_article_translations 
            SET description = \'<div class="gjs-text-ckeditor">\' || description || \'</div>\'
            WHERE description IS NOT NULL AND description NOT LIKE \'%gjs-text-ckeditor%\' 
        ');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

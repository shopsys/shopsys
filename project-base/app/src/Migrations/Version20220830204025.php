<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220830204025 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            UPDATE mail_templates 
            SET body = \'<div class="gjs-text-ckeditor">\' || body || \'</div>\'
            WHERE body IS NOT NULL AND body NOT LIKE \'%gjs-text-ckeditor%\' 
        ');

        $this->sql('
            UPDATE articles 
            SET text = \'<div class="gjs-text-ckeditor">\' || text || \'</div>\'
            WHERE text IS NOT NULL AND text NOT LIKE \'%gjs-text-ckeditor%\' 
        ');

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

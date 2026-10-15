<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261015092748 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE blog_article_translations ADD content JSONB DEFAULT NULL');
        $this->sql('ALTER TABLE blog_article_translations RENAME COLUMN description TO legacy_grapesjs_description');
        $this->sql('ALTER TABLE articles ADD content JSONB DEFAULT NULL');
        $this->sql('ALTER TABLE articles RENAME COLUMN text TO legacy_grapesjs_text');
    }
}

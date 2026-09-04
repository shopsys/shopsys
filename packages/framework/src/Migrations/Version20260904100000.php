<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260904100000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $tableNames = [
            'product_domains',
            'category_domains',
            'blog_article_domains',
            'blog_category_domains',
            'brand_domains',
            'articles',
            'ready_category_seo_mixes',
        ];

        foreach ($tableNames as $tableName) {
            $this->sql(sprintf('ALTER TABLE %s ADD seo_meta_robots VARCHAR(30) DEFAULT NULL', $tableName));
            $this->sql(sprintf('ALTER TABLE %s ADD seo_canonical_url TEXT DEFAULT NULL', $tableName));
        }
    }
}

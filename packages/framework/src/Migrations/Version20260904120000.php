<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260904120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE seo_page_domains RENAME COLUMN canonical_url TO seo_canonical_url');
        $this->sql('ALTER TABLE seo_page_domains ADD seo_h1 TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE seo_page_domains ADD seo_meta_robots VARCHAR(30) DEFAULT NULL');
    }
}

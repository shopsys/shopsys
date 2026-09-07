<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260907100000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stores ADD seo_title TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE stores ADD seo_meta_description TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE stores ADD seo_h1 TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE stores ADD seo_meta_robots VARCHAR(30) DEFAULT NULL');
        $this->sql('ALTER TABLE stores ADD seo_canonical_url TEXT DEFAULT NULL');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260903120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE ready_category_seo_mixes RENAME COLUMN title TO seo_title');
        $this->sql('ALTER TABLE ready_category_seo_mixes RENAME COLUMN meta_description TO seo_meta_description');
        $this->sql('ALTER TABLE ready_category_seo_mixes RENAME COLUMN h1 TO seo_h1');
        $this->sql('ALTER TABLE ready_category_seo_mixes ALTER seo_h1 DROP NOT NULL');
    }
}

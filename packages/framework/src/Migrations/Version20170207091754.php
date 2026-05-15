<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20170207091754 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE category_domains ADD seo_title TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE category_domains ADD seo_meta_description TEXT DEFAULT NULL');
    }
}

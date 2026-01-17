<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250825101232 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE seo_page_domains SET page_slug = \'/\' WHERE page_slug = \'homepage\'');
    }
}

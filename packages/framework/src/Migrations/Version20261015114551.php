<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261015114551 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE mail_templates ADD content JSONB DEFAULT NULL');
        $this->sql('ALTER TABLE mail_templates RENAME COLUMN body TO legacy_grapesjs_body');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20251205152525 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE closed_days ADD is_public_holiday BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE closed_days ALTER is_public_holiday DROP DEFAULT');
    }
}

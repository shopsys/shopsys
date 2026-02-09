<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250324141622 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->isAppMigrationNotInstalledRemoveIfExists('Version20201115140641');
        $this->sql('DROP TABLE IF EXISTS linked_categories');
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}

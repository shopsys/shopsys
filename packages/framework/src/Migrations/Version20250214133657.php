<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250214133657 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM setting_values WHERE name = \'imageStructureMigratedForProxy\'');
        $this->sql('DELETE FROM migrations WHERE version = \'Shopsys\FrameworkBundle\Migrations\Version20231020173331\'');
    }
}

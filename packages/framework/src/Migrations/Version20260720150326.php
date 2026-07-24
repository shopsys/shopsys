<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260720150326 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM setting_values WHERE name = \'fileStructureMigratedForRelations\'');
        $this->sql('DELETE FROM migrations WHERE version = \'Shopsys\FrameworkBundle\Migrations\Version20240730113802\'');
    }
}

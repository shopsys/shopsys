<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250506081245 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            'DELETE FROM migrations WHERE version = :version;',
            ['version' => $this->prefixAppMigrationVersion('Version20200221155940')],
        );
        $this->sql(
            'DELETE FROM migrations WHERE version = :version;',
            ['version' => $this->prefixAppMigrationVersion('Version20200714071640')],
        );
    }
}

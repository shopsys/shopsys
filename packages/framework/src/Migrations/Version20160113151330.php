<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160113151330 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $sql = 'ALTER TABLE scripts
            ADD COLUMN placement TEXT NOT NULL';
        $this->sql($sql);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20160108183213 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $sql = 'CREATE TABLE scripts
            (id SERIAL NOT NULL, name TEXT NOT NULL, code TEXT NOT NULL, PRIMARY KEY(id));';
        $this->sql($sql);
    }
}

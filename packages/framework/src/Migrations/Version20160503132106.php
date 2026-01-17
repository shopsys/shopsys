<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160503132106 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE flags ADD visible BOOLEAN NOT NULL DEFAULT TRUE;');
        $this->sql('ALTER TABLE flags ALTER visible DROP DEFAULT;');
    }
}

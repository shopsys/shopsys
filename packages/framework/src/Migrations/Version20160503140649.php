<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160503140649 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE parameter_titles ADD visible BOOLEAN NOT NULL DEFAULT TRUE;');
        $this->sql('ALTER TABLE parameter_titles ALTER visible DROP DEFAULT;');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240712070530 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE uploaded_files_relations ADD position INT NOT NULL');
    }
}

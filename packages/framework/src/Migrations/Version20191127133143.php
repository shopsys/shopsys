<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191127133143 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE uploaded_files ADD position INT NOT NULL DEFAULT 0');
        $this->sql('ALTER TABLE uploaded_files ALTER position DROP DEFAULT');
    }
}

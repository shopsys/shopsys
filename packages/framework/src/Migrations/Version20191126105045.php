<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20191126105045 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE uploaded_files ADD type VARCHAR(100) NOT NULL DEFAULT \'default\'');
        $this->sql('ALTER TABLE uploaded_files ALTER type DROP DEFAULT');
    }
}

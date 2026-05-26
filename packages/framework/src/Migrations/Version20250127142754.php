<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250127142754 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints ADD resolution VARCHAR(20) NOT NULL DEFAULT \'fix\'');
        $this->sql('ALTER TABLE complaints ALTER resolution DROP DEFAULT');
        $this->sql('ALTER TABLE complaints ADD bank_account_number VARCHAR(34) DEFAULT NULL');
    }
}

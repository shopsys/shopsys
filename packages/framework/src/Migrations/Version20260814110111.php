<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260814110111 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE withdrawal_requests ADD confirmed BOOLEAN NOT NULL DEFAULT TRUE');
        $this->sql('ALTER TABLE withdrawal_requests ALTER confirmed DROP DEFAULT');
        $this->sql('ALTER TABLE withdrawal_requests ADD confirmation_hash VARCHAR(64) DEFAULT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_3E7DE8A64F6BFE ON withdrawal_requests (confirmation_hash)');
    }
}

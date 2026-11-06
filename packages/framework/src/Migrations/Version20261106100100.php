<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261106100100 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints ADD actual_resolution VARCHAR(20) DEFAULT NULL');
        $this->sql('
            UPDATE complaints
            SET actual_resolution = resolution
            WHERE status_id IN (SELECT id FROM complaint_statuses WHERE closed = TRUE)
        ');
    }
}

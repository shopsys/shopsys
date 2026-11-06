<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261106100000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaint_statuses ADD closed BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE complaint_statuses ALTER closed DROP DEFAULT');
        $this->sql('UPDATE complaint_statuses SET closed = TRUE WHERE status_type = :resolvedStatusType', [
            'resolvedStatusType' => 'resolved',
        ]);
    }
}

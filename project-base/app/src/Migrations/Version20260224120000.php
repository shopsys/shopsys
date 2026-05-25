<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260224120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE9909C13F');
        $this->sql('ALTER TABLE orders DROP CONSTRAINT FK_E52FFDEE4C3A3BB');
        $this->sql('DROP INDEX IDX_E52FFDEE9909C13F');
        $this->sql('DROP INDEX IDX_E52FFDEE4C3A3BB');
        $this->sql('ALTER TABLE orders DROP COLUMN transport_id');
        $this->sql('ALTER TABLE orders DROP COLUMN payment_id');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20251020151517 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}

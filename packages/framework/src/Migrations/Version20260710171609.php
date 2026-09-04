<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260710171609 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE INDEX IDX_D510E302115F0EE5 ON gift_vouchers (domain_id)');
    }
}

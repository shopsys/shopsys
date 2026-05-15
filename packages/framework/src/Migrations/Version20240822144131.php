<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240822144131 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE gopay_payment_methods ADD available BOOLEAN NOT NULL DEFAULT TRUE');
        $this->sql('ALTER TABLE gopay_payment_methods ALTER available DROP DEFAULT');
    }
}

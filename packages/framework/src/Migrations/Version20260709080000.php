<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260709080000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX gopay_payment_method_unique');
        $this->sql('CREATE UNIQUE INDEX gopay_payment_method_unique ON gopay_payment_methods (domain_id, identifier, currency_id)');
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}

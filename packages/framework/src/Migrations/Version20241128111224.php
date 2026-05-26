<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241128111224 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD free_transport_and_payment_applied BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE orders ALTER free_transport_and_payment_applied DROP DEFAULT');
    }
}

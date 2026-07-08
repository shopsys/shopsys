<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260618191555 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD personal_pickup_only BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE products ALTER personal_pickup_only DROP DEFAULT');
    }
}

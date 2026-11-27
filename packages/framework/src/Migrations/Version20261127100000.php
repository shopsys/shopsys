<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261127100000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE carts ADD pickup_place_closed_dates JSON NOT NULL DEFAULT \'[]\'');
        $this->sql('ALTER TABLE carts ALTER pickup_place_closed_dates DROP DEFAULT');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240610181711 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200428092429')) {
            $this->sql('ALTER TABLE orders ALTER first_name DROP NOT NULL');
            $this->sql('ALTER TABLE orders ALTER last_name DROP NOT NULL');
            $this->sql('ALTER TABLE orders ALTER delivery_first_name DROP NOT NULL');
            $this->sql('ALTER TABLE orders ALTER delivery_last_name DROP NOT NULL');
        }

        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200622122343')) {
            $this->sql('ALTER TABLE orders ADD gtm_coupon VARCHAR(64) DEFAULT NULL');
        }
    }
}

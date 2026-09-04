<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260826120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ADD watched_additional_service_prices JSON DEFAULT \'[]\' NOT NULL');
        $this->sql('ALTER TABLE cart_items ALTER watched_additional_service_prices DROP DEFAULT');
    }
}

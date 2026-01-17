<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250903201421 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD is_allowed_negative_stock BOOLEAN NOT NULL DEFAULT TRUE');
        $this->sql('ALTER TABLE products ALTER is_allowed_negative_stock DROP DEFAULT ');
    }
}

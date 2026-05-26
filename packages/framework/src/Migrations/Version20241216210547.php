<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241216210547 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stores ADD email VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE stores ADD phone VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE stores ADD directions TEXT DEFAULT NULL');
    }
}

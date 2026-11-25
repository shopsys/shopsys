<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261125150000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE packetery_pickup_points (
                id VARCHAR(50) NOT NULL,
                regular_opening_hours JSON NOT NULL,
                upcoming_opening_hours JSON DEFAULT NULL,
                upcoming_opening_hours_since DATE DEFAULT NULL,
                exception_opening_hours JSON NOT NULL,
                PRIMARY KEY (id)
            )');
    }
}

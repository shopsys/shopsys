<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260720192055 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transports ADD delivery_days_of_week JSON NOT NULL DEFAULT \'[1, 2, 3, 4, 5]\'');
        $this->sql('ALTER TABLE transports ADD delivers_on_public_holidays BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE transports ADD delivers_on_internal_closed_days BOOLEAN NOT NULL DEFAULT FALSE');

        $this->sql('ALTER TABLE transports ALTER COLUMN delivery_days_of_week DROP DEFAULT');
        $this->sql('ALTER TABLE transports ALTER COLUMN delivers_on_public_holidays DROP DEFAULT');
        $this->sql('ALTER TABLE transports ALTER COLUMN delivers_on_internal_closed_days DROP DEFAULT');
    }
}

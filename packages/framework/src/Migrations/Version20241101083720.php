<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241101083720 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE parameter_groups ADD position INT NOT NULL DEFAULT 0');
        $this->sql('WITH ordered_groups AS (
                    SELECT id,
                           ROW_NUMBER() OVER (ORDER BY ordering_priority DESC, id ASC) - 1 AS new_position
                    FROM parameter_groups
                )
                UPDATE parameter_groups
                SET position = ordered_groups.new_position
                FROM ordered_groups
                WHERE parameter_groups.id = ordered_groups.id');
        $this->sql('ALTER TABLE parameter_groups ALTER position DROP DEFAULT');
        $this->sql('ALTER TABLE parameter_groups DROP ordering_priority');
    }
}

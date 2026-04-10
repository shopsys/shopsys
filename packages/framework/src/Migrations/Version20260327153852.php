<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260327153852 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE administrator_pinned_menu_items (
                administrator_id INT NOT NULL,
                route_name VARCHAR(255) NOT NULL,
                position INT NOT NULL,
                PRIMARY KEY (administrator_id, route_name)
            )
        ');

        $this->sql('
            ALTER TABLE administrator_pinned_menu_items
                ADD CONSTRAINT FK_admin_pinned_menu_item_administrator
                FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE
        ');
    }
}

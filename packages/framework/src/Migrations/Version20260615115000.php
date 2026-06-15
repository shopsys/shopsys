<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260615115000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE navigation_items ADD type VARCHAR(32) DEFAULT \'link\' NOT NULL');
        $this->sql('ALTER TABLE navigation_items ALTER url DROP NOT NULL');
        $this->sql('UPDATE navigation_items SET type = \'categories\' WHERE EXISTS (SELECT 1 FROM navigation_item_categories WHERE navigation_item_categories.navigation_item_id = navigation_items.id)');
        $this->sql('ALTER TABLE navigation_items ALTER type DROP DEFAULT');
    }
}

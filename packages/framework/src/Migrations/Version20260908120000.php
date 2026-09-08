<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260908120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_list_items ADD position INT DEFAULT NULL');
        $this->sql('UPDATE product_list_items AS item SET position = ordered.position FROM (
            SELECT id, (ROW_NUMBER() OVER (PARTITION BY product_list_id ORDER BY created_at DESC, id DESC) - 1)::INT AS position
            FROM product_list_items
        ) AS ordered WHERE item.id = ordered.id');
        $this->sql('ALTER TABLE product_list_items ALTER position SET NOT NULL');
    }
}

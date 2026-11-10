<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261013102900 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE cart_item_relations (cart_item_id INT NOT NULL, related_item_id INT NOT NULL, PRIMARY KEY (cart_item_id, related_item_id))');
        $this->sql('CREATE INDEX IDX_B288FD81E9B59A59 ON cart_item_relations (cart_item_id)');
        $this->sql('CREATE UNIQUE INDEX UNIQ_B288FD812D7698FB ON cart_item_relations (related_item_id)');
        $this->sql('ALTER TABLE cart_item_relations ADD CONSTRAINT FK_B288FD81E9B59A59 FOREIGN KEY (cart_item_id) REFERENCES cart_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE cart_item_relations ADD CONSTRAINT FK_B288FD812D7698FB FOREIGN KEY (related_item_id) REFERENCES cart_items (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

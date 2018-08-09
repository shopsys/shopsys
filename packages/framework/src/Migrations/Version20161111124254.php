<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161111124254 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE cart_items RENAME COLUMN session_id TO cart_identifier');
    }

    public function down(Schema $schema)
    {
    }
}

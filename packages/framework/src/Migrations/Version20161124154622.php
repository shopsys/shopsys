<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161124154622 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE products_top ADD position INT NOT NULL DEFAULT 0');
        $this->sql('ALTER TABLE products_top ALTER position DROP DEFAULT');
    }

    public function down(Schema $schema)
    {
    }
}

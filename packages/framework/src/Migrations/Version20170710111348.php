<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20170710111348 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE product_domains ADD seo_h1 TEXT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
    }
}

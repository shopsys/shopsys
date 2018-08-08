<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20170207091754 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('ALTER TABLE category_domains ADD seo_title TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE category_domains ADD seo_meta_description TEXT DEFAULT NULL');
    }

    public function down(Schema $schema)
    {
    }
}

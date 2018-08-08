<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20170710120408 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE articles ADD seo_h1 TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
    }
}

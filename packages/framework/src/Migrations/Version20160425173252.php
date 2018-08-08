<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160425173252 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE product_domains ADD COLUMN short_description TEXT DEFAULT NULL;');
    }

    public function down(Schema $schema): void
    {
    }
}

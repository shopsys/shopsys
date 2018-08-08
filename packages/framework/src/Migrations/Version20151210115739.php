<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20151210115739 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = 'ALTER TABLE products
            RENAME COLUMN visible TO calculated_visibility';
        $this->sql($sql);
    }

    public function down(Schema $schema): void
    {
    }
}

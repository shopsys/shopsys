<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20160105140120 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = 'ALTER TABLE order_status_translations
            ALTER name SET NOT NULL;';
        $this->sql($sql);
    }

    public function down(Schema $schema): void
    {
    }
}

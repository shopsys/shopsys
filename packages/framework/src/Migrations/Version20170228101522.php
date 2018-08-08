<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20170228101522 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('CREATE INDEX IDX_C52F9B1F12469DE2115F0EE5 ON product_category_domains (category_id, domain_id)');
    }

    public function down(Schema $schema)
    {
    }
}

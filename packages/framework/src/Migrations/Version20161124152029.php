<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20161124152029 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->sql('DROP INDEX "product_domain_unique"');
        $this->sql('DROP INDEX "idx_da6be6944584665a"');
        $this->sql('DROP INDEX "idx_da6be6944584665a115f0ee5"');
        $this->sql('ALTER TABLE products_top
            DROP CONSTRAINT "products_top_pkey",
            DROP id,
            ADD PRIMARY KEY (product_id, domain_id)
        ');
    }

    public function down(Schema $schema)
    {
    }
}

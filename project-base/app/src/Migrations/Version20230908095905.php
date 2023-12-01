<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20230908095905 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE category_domains DROP short_description');
        $this->sql('ALTER TABLE categories DROP svg_icon');
        $this->sql('ALTER TABLE categories DROP over_limit_quantity');

        // already moved migrations to framework, kept here until whole migration file can be removed
        $this->sql('ALTER TABLE stores DROP opening_hours');
        $this->sql('DROP TABLE product_stores');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

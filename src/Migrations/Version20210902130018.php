<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210902130018 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cart_items ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE cart_items SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE cart_items ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_BEF48445D17F50A6 ON cart_items (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

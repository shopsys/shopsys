<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240801114052 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_items ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE order_items SET uuid = uuid_generate_v4() WHERE uuid IS NULL');
        $this->sql('ALTER TABLE order_items ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_62809DB0D17F50A6 ON order_items (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210629054204 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stores ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE stores SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE stores ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_D5907CCCD17F50A6 ON stores (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

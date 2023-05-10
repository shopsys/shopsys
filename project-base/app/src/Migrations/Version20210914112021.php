<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20210914112021 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE delivery_addresses ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE delivery_addresses SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE delivery_addresses ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_2BAF3984D17F50A6 ON delivery_addresses (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200319124638 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_users ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE customer_users SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE customer_users ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_DAB6D0D2D17F50A6 ON customer_users (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

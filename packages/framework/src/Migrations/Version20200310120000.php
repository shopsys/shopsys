<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200310120000 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payments ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE payments SET uuid = :uuid', ['uuid' => Uuid::uuid4()->toString()]);
        $this->sql('ALTER TABLE payments ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_65D29B32D17F50A6 ON payments (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20190930140221 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE categories SET uuid = :uuid', ['uuid' => Uuid::uuid4()->toString()]);
        $this->sql('ALTER TABLE categories ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_3AF34668D17F50A6 ON categories (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

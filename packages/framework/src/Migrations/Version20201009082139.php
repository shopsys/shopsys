<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20201009082139 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE adverts ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE adverts SET uuid = :uuid', ['uuid' => Uuid::uuid4()->toString()]);
        $this->sql('ALTER TABLE adverts ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_8C88E777D17F50A6 ON adverts (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

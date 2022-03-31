<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Ramsey\Uuid\Uuid;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200930074334 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE parameter_values ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE parameter_values SET uuid = :uuid', ['uuid' => Uuid::uuid4()->toString()]);
        $this->sql('ALTER TABLE parameter_values ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_DED94617D17F50A6 ON parameter_values (uuid)');

        $this->sql('ALTER TABLE parameters ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE parameters SET uuid = :uuid', ['uuid' => Uuid::uuid4()->toString()]);
        $this->sql('ALTER TABLE parameters ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_69348FED17F50A6 ON parameters (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

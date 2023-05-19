<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200310130000 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transports ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE transports SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE transports ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_C7BE69E5D17F50A6 ON transports (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

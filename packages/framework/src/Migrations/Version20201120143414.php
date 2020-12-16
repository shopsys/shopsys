<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20201120143414 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE flags ADD uuid UUID DEFAULT NULL');
        $this->sql('UPDATE flags SET uuid = uuid_generate_v4()');
        $this->sql('ALTER TABLE flags ALTER uuid SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_B0541BAD17F50A6 ON flags (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

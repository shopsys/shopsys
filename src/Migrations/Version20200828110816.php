<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200828110816 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payments ADD external_id INT');
        $this->sql('UPDATE payments SET external_id = id');
        $this->sql('ALTER TABLE payments ALTER COLUMN external_id SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_65D29B329F75D7B0 ON payments (external_id)');
        $this->sql('ALTER TABLE transports ADD external_id INT');
        $this->sql('UPDATE transports SET external_id = id');
        $this->sql('ALTER TABLE transports ALTER COLUMN external_id SET NOT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_C7BE69E59F75D7B0 ON transports (external_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

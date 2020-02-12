<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200210142710 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO setting_values (name, domain_id, value, type) VALUES
            (\'scontoBridgeTransferCustomersLastUpdatedDatetime\', 0, \'1970-01-01T00:00:00.000000\', \'string\')
        ');
        $this->sql('ALTER TABLE customer_users ADD erp_customer_number INT DEFAULT NULL');
        $this->sql('CREATE UNIQUE INDEX UNIQ_DAB6D0D2913380ED ON customer_users (erp_customer_number)');

        $this->sql('ALTER TABLE customer_users ALTER gender DROP NOT NULL');

        $this->sql('ALTER TABLE customer_users ALTER first_name DROP NOT NULL');
        $this->sql('ALTER TABLE customer_users ALTER last_name DROP NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

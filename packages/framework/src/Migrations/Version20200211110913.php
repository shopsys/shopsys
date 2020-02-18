<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200211110913 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_users ADD default_delivery_address_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                customer_users
            ADD
                CONSTRAINT FK_DAB6D0D2422ED30C FOREIGN KEY (default_delivery_address_id) REFERENCES delivery_addresses (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_DAB6D0D2422ED30C ON customer_users (default_delivery_address_id)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

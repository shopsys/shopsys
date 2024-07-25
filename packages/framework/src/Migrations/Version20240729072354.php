<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240729072354 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE delivery_addresses SET street = NULL WHERE street = \'\'');
        $this->sql('UPDATE delivery_addresses SET city = NULL WHERE city = \'\'');
        $this->sql('UPDATE delivery_addresses SET postcode = NULL WHERE postcode = \'\'');
        $this->sql('UPDATE delivery_addresses SET first_name = NULL WHERE first_name = \'\'');
        $this->sql('UPDATE delivery_addresses SET last_name = NULL WHERE last_name = \'\'');
        $this->sql('UPDATE customer_users SET first_name = NULL WHERE first_name = \'\'');
        $this->sql('UPDATE customer_users SET last_name = NULL WHERE last_name = \'\'');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

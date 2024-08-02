<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240724062626 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE delivery_addresses ALTER country_id DROP NOT NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER street DROP NOT NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER city DROP NOT NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER postcode DROP NOT NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER first_name DROP NOT NULL');
        $this->sql('ALTER TABLE delivery_addresses ALTER last_name DROP NOT NULL');
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

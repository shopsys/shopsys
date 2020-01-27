<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200127085928 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE delivery_addresses ADD customer_id INT NOT NULL DEFAULT 0');
        $this->sql('
            ALTER TABLE
                delivery_addresses
            ADD
                CONSTRAINT FK_2BAF39849395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_2BAF39849395C3F3 ON delivery_addresses (customer_id)');
        $this->sql('UPDATE delivery_addresses SET customer_id = (SELECT customer_id FROM customer_users WHERE delivery_address_id = delivery_addresses.id)');
        $this->sql('ALTER TABLE customer_users DROP delivery_address_id');
        $this->sql('ALTER TABLE delivery_addresses ALTER customer_id DROP DEFAULT');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

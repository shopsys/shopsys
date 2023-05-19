<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191205145631 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('CREATE TABLE customers (id SERIAL NOT NULL, PRIMARY KEY(id))');
        $this->sql('ALTER TABLE billing_addresses ADD customer_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                billing_addresses
            ADD
                CONSTRAINT FK_DBD917489395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_DBD917489395C3F3 ON billing_addresses (customer_id)');
        $this->sql('ALTER TABLE users ADD customer_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                users
            ADD
                CONSTRAINT FK_1483A5E99395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_1483A5E99395C3F3 ON users (customer_id)');

        $this->sql('INSERT INTO customers (id) SELECT id FROM users');
        $this->sql(
            'SELECT SETVAL(pg_get_serial_sequence(\'customers\', \'id\'), COALESCE((SELECT MAX(id) FROM users) + 1, 1), false)'
        );

        $this->sql('UPDATE users SET customer_id=id');

        $users = $this->sql('SELECT id, billing_address_id FROM users')->fetchAllAssociative();

        foreach ($users as $user) {
            $this->sql(
                'UPDATE billing_addresses SET customer_id=? WHERE id=?',
                [$user['id'], $user['billing_address_id']]
            );
        }

        $this->sql('ALTER TABLE users ALTER customer_id SET NOT NULL');
        $this->sql('ALTER TABLE billing_addresses ALTER customer_id SET NOT NULL');

        $this->sql('ALTER TABLE "users" DROP "billing_address_id"');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

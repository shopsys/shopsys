<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191216143436 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('DROP INDEX email_domain');
        $this->sql('ALTER TABLE orders DROP CONSTRAINT fk_e52ffdee9395c3f3');
        $this->sql('ALTER TABLE orders DROP CONSTRAINT fk_e52ffdeea76ed395');
        $this->sql('ALTER TABLE carts DROP CONSTRAINT fk_4e004aaca76ed395');

        $this->sql('
            CREATE TABLE customer_users (
                id SERIAL NOT NULL,
                customer_id INT NOT NULL,
                delivery_address_id INT DEFAULT NULL,
                pricing_group_id INT NOT NULL,
                first_name VARCHAR(100) NOT NULL,
                last_name VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                password VARCHAR(100) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                domain_id INT NOT NULL,
                reset_password_hash VARCHAR(50) DEFAULT NULL,
                reset_password_hash_valid_through TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                telephone VARCHAR(30) DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_DAB6D0D29395C3F3 ON customer_users (customer_id)');
        $this->sql('CREATE UNIQUE INDEX UNIQ_DAB6D0D2EBF23851 ON customer_users (delivery_address_id)');
        $this->sql('CREATE INDEX IDX_DAB6D0D2BE4A29AF ON customer_users (pricing_group_id)');
        $this->sql('CREATE INDEX IDX_DAB6D0D2E7927C74 ON customer_users (email)');

        $this->sql('CREATE UNIQUE INDEX email_domain ON customer_users (email, domain_id)');
        $this->sql('
            ALTER TABLE
                customer_users
            ADD
                CONSTRAINT FK_DAB6D0D29395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                customer_users
            ADD
                CONSTRAINT FK_DAB6D0D2EBF23851 FOREIGN KEY (delivery_address_id) REFERENCES delivery_addresses (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                customer_users
            ADD
                CONSTRAINT FK_DAB6D0D2BE4A29AF FOREIGN KEY (pricing_group_id) REFERENCES pricing_groups (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->sql('
INSERT INTO customer_users (id, customer_id, delivery_address_id, pricing_group_id, first_name, last_name, email, password, created_at, last_login, domain_id, reset_password_hash, reset_password_hash_valid_through, telephone)
SELECT id, customer_id, delivery_address_id, pricing_group_id, first_name, last_name, email, password, created_at, last_login, domain_id, reset_password_hash, reset_password_hash_valid_through, telephone FROM users
');

        $this->sql('ALTER TABLE carts RENAME COLUMN user_id TO customer_user_id');
        $this->sql('
            ALTER TABLE
                carts
            ADD
                CONSTRAINT FK_4E004AACBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_4E004AACBBB3772B ON carts (customer_user_id)');

        $this->sql('ALTER TABLE orders RENAME COLUMN user_id TO customer_user_id');
        $this->sql('
            ALTER TABLE
                orders
            ADD
                CONSTRAINT FK_E52FFDEEBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE
            SET
                NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER INDEX idx_e52ffdee9395c3f3 RENAME TO IDX_E52FFDEEBBB3772B');

        $this->sql('DROP TABLE users');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

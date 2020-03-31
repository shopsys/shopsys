<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200327080840 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE customer_user_refresh_token_chain (
                uuid UUID NOT NULL,
                customer_user_id INT NOT NULL,
                token_chain VARCHAR(255) NOT NULL,
                expired_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(uuid)
            )');
        $this->sql('CREATE INDEX IDX_DA9A5BFDBBB3772B ON customer_user_refresh_token_chain (customer_user_id)');
        $this->sql('
            ALTER TABLE
                customer_user_refresh_token_chain
            ADD
                CONSTRAINT FK_DA9A5BFDBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

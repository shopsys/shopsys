<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200511110953 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_refresh_token_chain DROP CONSTRAINT FK_DA9A5BFDBBB3772B');
        $this->sql('
            ALTER TABLE
                customer_user_refresh_token_chain
            ADD
                CONSTRAINT FK_DA9A5BFDBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

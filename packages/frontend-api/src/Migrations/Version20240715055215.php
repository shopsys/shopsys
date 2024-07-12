<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240715055215 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE customer_user_login_types (
                login_type TEXT NOT NULL,
                customer_user_id INT NOT NULL,
                PRIMARY KEY(customer_user_id, login_type)
            )');
        $this->sql('CREATE INDEX IDX_E6FAD52DBBB3772B ON customer_user_login_types (customer_user_id)');
        $this->sql('
            CREATE INDEX IDX_E6FAD52DBBB3772B4AACDC6C ON customer_user_login_types (customer_user_id, login_type)');
        $this->sql('
            ALTER TABLE
                customer_user_login_types
            ADD
                CONSTRAINT FK_E6FAD52DBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250730125606 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE login_as_user_exchange_tokens (
                token VARCHAR(64) NOT NULL,
                customer_user_id INT NOT NULL,
                administrator_id INT NOT NULL,
                expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(token)
            )');
        $this->sql('CREATE INDEX IDX_B15C9D4FBBB3772B ON login_as_user_exchange_tokens (customer_user_id)');
        $this->sql('CREATE INDEX IDX_B15C9D4F4B09E92C ON login_as_user_exchange_tokens (administrator_id)');
        $this->sql('
            ALTER TABLE
                login_as_user_exchange_tokens
            ADD
                CONSTRAINT FK_B15C9D4FBBB3772B FOREIGN KEY (customer_user_id) REFERENCES customer_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                login_as_user_exchange_tokens
            ADD
                CONSTRAINT FK_B15C9D4F4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

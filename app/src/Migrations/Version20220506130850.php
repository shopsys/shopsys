<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20220506130850 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE customer_user_refresh_token_chain ADD administrator_id INT DEFAULT NULL');
        $this->sql('
            ALTER TABLE
                customer_user_refresh_token_chain
            ADD
                CONSTRAINT FK_DA9A5BFD4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('CREATE INDEX IDX_DA9A5BFD4B09E92C ON customer_user_refresh_token_chain (administrator_id)');
        $this->sql('ALTER TABLE administrators ADD uuid UUID NOT NULL DEFAULT uuid_generate_v4()');
        $this->sql('ALTER TABLE administrators ALTER uuid DROP DEFAULT;');
        $this->sql('CREATE UNIQUE INDEX UNIQ_73A716FD17F50A6 ON administrators (uuid)');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

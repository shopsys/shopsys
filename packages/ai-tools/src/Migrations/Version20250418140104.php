<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250418140104 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE chat_messages (
                id SERIAL NOT NULL,
                chat_id INT NOT NULL,
                question TEXT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                answer TEXT DEFAULT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE INDEX IDX_EF20C9A61A9A7125 ON chat_messages (chat_id)');
        $this->sql('
            CREATE TABLE chats (
                id SERIAL NOT NULL,
                agent_id INT NOT NULL,
                identifier UUID NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('CREATE UNIQUE INDEX UNIQ_2D68180F772E836A ON chats (identifier)');
        $this->sql('CREATE INDEX IDX_2D68180F3414710B ON chats (agent_id)');
        $this->sql('
            CREATE TABLE chat_agents (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                enabled BOOLEAN NOT NULL,
                PRIMARY KEY(id)
            )');
        $this->sql('
            ALTER TABLE
                chat_messages
            ADD
                CONSTRAINT FK_EF20C9A61A9A7125 FOREIGN KEY (chat_id) REFERENCES chats (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                chats
            ADD
                CONSTRAINT FK_2D68180F3414710B FOREIGN KEY (agent_id) REFERENCES chat_agents (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}

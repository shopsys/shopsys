<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250518121453 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE chat_agent_chat_vector_stores (
                agent_id INT NOT NULL,
                vector_store_id INT NOT NULL,
                PRIMARY KEY(agent_id, vector_store_id)
            )');
        $this->sql('CREATE INDEX IDX_5DCBB0813414710B ON chat_agent_chat_vector_stores (agent_id)');
        $this->sql('CREATE INDEX IDX_5DCBB08126B52E62 ON chat_agent_chat_vector_stores (vector_store_id)');
        $this->sql('
            ALTER TABLE
                chat_agent_chat_vector_stores
            ADD
                CONSTRAINT FK_5DCBB0813414710B FOREIGN KEY (agent_id) REFERENCES chat_agents (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('
            ALTER TABLE
                chat_agent_chat_vector_stores
            ADD
                CONSTRAINT FK_5DCBB08126B52E62 FOREIGN KEY (vector_store_id) REFERENCES chat_vector_stores (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->sql('ALTER TABLE chat_vector_stores ADD data_structure JSON NOT NULL DEFAULT \'{}\'');
        $this->sql('ALTER TABLE chat_vector_stores ALTER data_structure DROP DEFAULT ');
    }
}

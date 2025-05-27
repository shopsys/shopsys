<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250530105315 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE chat_agents ADD COLUMN ai_model_id integer DEFAULT NULL');


        $this->sql('CREATE INDEX IDX_F24A9C4F66933187 ON chat_agents (ai_model_id)');

        $this->sql(
            <<<SQL
ALTER TABLE chat_agents
  ADD CONSTRAINT FK_F24A9C4F66933187
  FOREIGN KEY (ai_model_id) REFERENCES ai_models (id)
  NOT DEFERRABLE INITIALLY IMMEDIATE
SQL,
        );

        $aiModelId = $this->sql('SELECT id FROM ai_models WHERE name = \'gpt-3.5-turbo\'')->fetchOne();

        $this->sql('UPDATE chat_agents SET ai_model_id = :aiModelId WHERE model = \'gpt-3.5-turbo\'', ['aiModelId' => $aiModelId]);
        $this->sql('ALTER TABLE chat_agents DROP COLUMN model');
        $this->sql('ALTER TABLE chat_agents ALTER ai_model_id SET NOT NULL');
    }
}

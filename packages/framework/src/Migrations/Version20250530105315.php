<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250530105315 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql(<<<SQL
            ALTER TABLE ai_models
              ALTER COLUMN id DROP DEFAULT,
              ALTER COLUMN id TYPE integer USING id::integer
SQL
        );

        $this->sql('CREATE SEQUENCE ai_models_id_seq');
        $this->sql("SELECT setval('ai_models_id_seq', (SELECT MAX(id) FROM ai_models))");
        $this->sql("ALTER TABLE ai_models ALTER COLUMN id SET DEFAULT nextval('ai_models_id_seq')");

        $this->sql('ALTER TABLE ai_models ALTER COLUMN name SET NOT NULL');

        $this->sql('ALTER TABLE chat_agents ADD COLUMN ai_model_id integer DEFAULT NULL');
        $this->sql('ALTER TABLE chat_agents DROP COLUMN model');

        $this->sql('CREATE INDEX IDX_F24A9C4F66933187 ON chat_agents (ai_model_id)');

        $this->sql(<<<SQL
ALTER TABLE chat_agents
  ADD CONSTRAINT FK_F24A9C4F66933187
  FOREIGN KEY (ai_model_id) REFERENCES ai_models (id)
  NOT DEFERRABLE INITIALLY IMMEDIATE
SQL
        );
    }


}

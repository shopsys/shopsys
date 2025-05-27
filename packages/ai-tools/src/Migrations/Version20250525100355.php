<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250525100355 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('
            CREATE TABLE ai_models (
                id SERIAL NOT NULL,
                name VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT true,
                is_deprecated BOOLEAN NOT NULL DEFAULT false,
                api_source VARCHAR(255) NOT NULL,
                PRIMARY KEY(id)
            )');

        $this->sql('ALTER TABLE ai_models ALTER is_active DROP DEFAULT ');
        $this->sql('ALTER TABLE ai_models ALTER is_deprecated DROP DEFAULT ');

        $this->sql(
            'INSERT INTO ai_models (name, description, api_source, is_active, is_deprecated) VALUES (:name, :description, :api_source, :is_active, :is_deprecated)',
            [
                'name' => 'gpt-3.5-turbo',
                'description' => 'gpt-3.5-turbo',
                'api_source' => 'openai',
                'is_active' => 'true',
                'is_deprecated' => 'false',
            ],
        );
    }
}

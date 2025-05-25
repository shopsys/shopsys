<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250502180152 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE chat_agents ADD available_ai_functions JSON NOT NULL DEFAULT \'[]\'::json');
        $this->sql('ALTER TABLE chat_agents ALTER available_ai_functions DROP DEFAULT');

        $this->sql('INSERT INTO chat_agents (name, enabled, ai_model_id, setup, internal_key, available_ai_functions) VALUES (:name, :enabled, :ai_model_id, :setup, :internalKey, :availableFunctions)', [
            'name' => 'Article generator gpt-3.5-turbo',
            'enabled' => true,
            'ai_model_id' => 'gpt-3.5-turbo',
            'setup' => 'Jsi asistent pro vytváření článků podle zadaného tématu. Výstupem bude článek v odstavcích ve formátu html. Pokud bude v dotazu použito catnum produktu dohledej si název pomocí funkce getProductNameByCatnum, povolené locale jsou jen \'cs\' a \'en\'.',
            'internalKey' => 'articleGenerator',
            'availableFunctions' => json_encode(['getProductNameByCatnum', 'getCurrentLocale']),
        ]);
    }
}

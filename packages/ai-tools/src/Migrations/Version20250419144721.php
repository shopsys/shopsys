<?php

declare(strict_types=1);

namespace Shopsys\AiToolsBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250419144721 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE chat_agents ADD model VARCHAR(255) NOT NULL DEFAULT \'gpt-3.5-turbo\'');
        $this->sql('ALTER TABLE chat_agents ALTER model DROP DEFAULT ');
        $this->sql('ALTER TABLE chat_agents ADD setup TEXT NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE chat_agents ALTER setup DROP DEFAULT');
    }
}

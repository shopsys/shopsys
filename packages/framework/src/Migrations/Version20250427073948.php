<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250427073948 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE chat_messages ADD input_tokens INT DEFAULT NULL');
        $this->sql('ALTER TABLE chat_messages ALTER input_tokens DROP DEFAULT');

        $this->sql('ALTER TABLE chat_messages ADD output_tokens INT DEFAULT NULL');
        $this->sql('ALTER TABLE chat_messages ALTER output_tokens DROP DEFAULT');

        $this->sql('ALTER TABLE chat_messages ADD total_tokens INT DEFAULT NULL');
        $this->sql('ALTER TABLE chat_messages ALTER total_tokens DROP DEFAULT');

        $this->sql('ALTER TABLE chat_agents ADD internal_key VARCHAR(255) NOT NULL DEFAULT \'\'');
        $this->sql('ALTER TABLE chat_agents ALTER internal_key DROP DEFAULT');
    }
}

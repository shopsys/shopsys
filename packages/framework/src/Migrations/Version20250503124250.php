<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250503124250 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE chat_messages ADD function_call JSON DEFAULT NULL');
        $this->sql('ALTER TABLE chat_messages ALTER function_call DROP DEFAULT ');
        $this->sql('ALTER TABLE chat_messages ADD function_call_result JSON DEFAULT NULL');
        $this->sql('ALTER TABLE chat_messages ALTER function_call_result DROP DEFAULT');
        $this->sql('ALTER TABLE chat_messages ADD type VARCHAR(20) NOT NULL DEFAULT \'message\'');
        $this->sql('ALTER TABLE chat_messages ALTER type DROP DEFAULT');
    }
}

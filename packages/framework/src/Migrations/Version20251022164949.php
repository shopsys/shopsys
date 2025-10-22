<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20251022164949 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD withdrawal_first_name VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD withdrawal_last_name VARCHAR(100) DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD withdrawal_telephone VARCHAR(30) DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD withdrawal_email VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD withdrawal_note TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE orders ADD withdrawal_requested_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }
}

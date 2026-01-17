<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250407111015 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payment_transactions ADD external_payment_url VARCHAR(255) DEFAULT NULL');
        $this->sql('ALTER TABLE payment_transactions ADD external_payment_sub_status VARCHAR(255) DEFAULT NULL');
    }
}

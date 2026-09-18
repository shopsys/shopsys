<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260918094333 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE cron_modules ADD error_message_of_last_run TEXT DEFAULT NULL');
        $this->sql('ALTER TABLE cron_module_runs ADD error_message TEXT DEFAULT NULL');
    }
}

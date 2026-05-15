<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250302234333 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DELETE FROM cron_module_runs WHERE cron_module_id = \'Shopsys\\FrameworkBundle\\Component\\Error\\ErrorPageCronModule\'');
        $this->sql('DELETE FROM cron_modules WHERE service_id = \'Shopsys\\FrameworkBundle\\Component\\Error\\ErrorPageCronModule\'');
    }
}

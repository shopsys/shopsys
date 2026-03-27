<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180702111016 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $enabledModulesCount = $this->sqlQuery('SELECT count(*) FROM enabled_modules')->fetchOne();

        if ($enabledModulesCount > 0) {
            return;
        }

        $this->sql(
            'INSERT INTO "enabled_modules" ("name") VALUES '
            . '(\'productFilterCounts\'),'
            . '(\'productStockCalculations\')',
        );
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20151230012022 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            'CREATE TABLE cron_modules (module_id VARCHAR(255) NOT NULL, scheduled BOOLEAN NOT NULL, PRIMARY KEY(module_id));',
        );
    }
}

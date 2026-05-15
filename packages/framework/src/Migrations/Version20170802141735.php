<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20170802141735 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql(
            'CREATE TABLE plugin_data_values (
                plugin_name VARCHAR(255) NOT NULL,
                context VARCHAR(255) NOT NULL,
                key VARCHAR(255) NOT NULL,
                json_value TEXT NOT NULL,
                PRIMARY KEY(plugin_name, context, key)
            )',
        );
    }
}

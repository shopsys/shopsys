<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20180413102110 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DROP TABLE plugin_data_values');
    }
}

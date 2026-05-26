<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200219145345 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        if ($this->isAppMigrationNotInstalledRemoveIfExists('Version20200219145345')) {
            $this->sql('ALTER TABLE adverts ADD datetime_visible_from TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
            $this->sql('ALTER TABLE adverts ADD datetime_visible_to TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        }
    }
}

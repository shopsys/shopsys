<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240604152553 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->isAppMigrationNotInstalledRemoveIfExists('Version20200617101511');

        $this->sql('DROP TABLE IF EXISTS advert_category');
        $this->sql('DROP TABLE IF EXISTS entity');
    }
}

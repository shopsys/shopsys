<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20241216165539 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories ADD automated_filters JSON NOT NULL DEFAULT \'{}\'');
        $this->sql('ALTER TABLE categories ALTER automated_filters DROP DEFAULT');
    }
}

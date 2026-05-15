<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20191113231735 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE administrators ADD roles_changed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->sql('ALTER TABLE administrators ALTER roles_changed_at DROP DEFAULT');
    }
}

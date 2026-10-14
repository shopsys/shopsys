<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261002120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE flags ADD position INT NOT NULL DEFAULT 0');
        $this->sql('UPDATE flags SET position = id');
        $this->sql('ALTER TABLE flags ALTER position DROP DEFAULT');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20160419131007 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD ordering_priority INT NOT NULL DEFAULT 0;');
        $this->sql('ALTER TABLE products ALTER ordering_priority DROP DEFAULT;');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20230120123538 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('CREATE INDEX IDX_3AF34668DA439252 ON categories (lft)');
        $this->sql('CREATE INDEX IDX_3AF34668D5E02D69 ON categories (rgt)');
    }
}

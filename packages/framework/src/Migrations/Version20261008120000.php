<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261008120000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE carts RENAME COLUMN modified_at TO last_activity_at');
        $this->sql('ALTER TABLE carts ALTER COLUMN last_activity_at TYPE DATE');
        // the DC2Type comment of the former datetime_immutable column is not needed for the native DATE type
        $this->sql('COMMENT ON COLUMN carts.last_activity_at IS \'\'');
    }
}

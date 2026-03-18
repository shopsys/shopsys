<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260318194239 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE stock_domains ADD is_default BOOLEAN NOT NULL DEFAULT FALSE');

        $this->sql('
            UPDATE stock_domains sd
            SET is_default = s.is_default
            FROM stocks s
            WHERE sd.stock_id = s.id
        ');

        $this->sql('ALTER TABLE stock_domains ALTER COLUMN is_default DROP DEFAULT');

        $this->sql('ALTER TABLE stocks DROP COLUMN is_default');
    }
}

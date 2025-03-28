<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200707171540 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql("SELECT SETVAL(pg_get_serial_sequence('flags', 'id'), COALESCE((SELECT MAX(id) FROM flags) + 1, 1), false)");
        $this->sql("SELECT SETVAL(pg_get_serial_sequence('flag_translations', 'id'), COALESCE((SELECT MAX(id) FROM flag_translations) + 1, 1), false)");
    }
}

<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20200430122227 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE payments ADD hidden_by_go_pay BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE payments ALTER hidden_by_go_pay DROP DEFAULT');
    }
}

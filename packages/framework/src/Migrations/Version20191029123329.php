<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20191029123329 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE currencies ADD min_fraction_digits INT NOT NULL DEFAULT 2');
        $this->sql('ALTER TABLE currencies ALTER min_fraction_digits DROP DEFAULT');
    }
}

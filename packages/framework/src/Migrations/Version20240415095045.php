<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240415095045 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE orders ADD heureka_agreement BOOLEAN NOT NULL DEFAULT FALSE');
        $this->sql('ALTER TABLE orders ALTER heureka_agreement DROP DEFAULT');
    }
}

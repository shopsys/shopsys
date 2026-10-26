<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20261026090302 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE categories ADD visible_filter_parameters_count INT NOT NULL DEFAULT 5');
        $this->sql('ALTER TABLE categories ALTER visible_filter_parameters_count DROP DEFAULT');
    }
}

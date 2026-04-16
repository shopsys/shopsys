<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260416140346 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('DROP SEQUENCE IF EXISTS heureka_category_id_seq');
    }
}

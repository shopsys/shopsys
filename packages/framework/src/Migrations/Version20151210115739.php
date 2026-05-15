<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20151210115739 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $sql = 'ALTER TABLE products
            RENAME COLUMN visible TO calculated_visibility';
        $this->sql($sql);
    }
}

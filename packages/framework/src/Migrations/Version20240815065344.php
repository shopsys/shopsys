<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240815065344 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE sales_representatives ALTER first_name DROP NOT NULL');
        $this->sql('ALTER TABLE sales_representatives ALTER last_name DROP NOT NULL');
        $this->sql('ALTER TABLE sales_representatives ALTER email DROP NOT NULL');
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260312111837 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE sales_representatives RENAME COLUMN telephone TO telephone_number');
        $this->sql('ALTER TABLE sales_representatives ADD telephone_prefix VARCHAR(10) DEFAULT NULL');
        $this->sql('ALTER TABLE sales_representatives ADD telephone_prefix_country_code VARCHAR(2) DEFAULT NULL');
    }
}

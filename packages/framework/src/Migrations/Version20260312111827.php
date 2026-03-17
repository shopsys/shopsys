<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20260312111827 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE complaints RENAME COLUMN delivery_telephone TO delivery_telephone_number');
        $this->sql('ALTER TABLE complaints ADD delivery_telephone_prefix VARCHAR(10) DEFAULT NULL');
        $this->sql('ALTER TABLE complaints ADD delivery_telephone_prefix_country_code VARCHAR(2) DEFAULT NULL');
    }
}

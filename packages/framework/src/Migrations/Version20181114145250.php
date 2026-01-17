<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20181114145250 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE pricing_groups DROP COLUMN coefficient');
        $this->sql('ALTER TABLE products DROP COLUMN price');
        $this->sql('ALTER TABLE products DROP COLUMN price_calculation_type');
    }
}

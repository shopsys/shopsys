<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20231106135517 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products DROP COLUMN export_product');
        $this->sql('DROP TRIGGER IF EXISTS mark_product_for_export ON product_visibilities');
        $this->sql('DROP FUNCTION IF EXISTS set_export_product_by_product_visibility');
    }
}

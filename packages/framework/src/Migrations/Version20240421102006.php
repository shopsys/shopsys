<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20240421102006 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE order_items RENAME COLUMN price_without_vat TO unit_price_without_vat');
        $this->sql('ALTER TABLE order_items RENAME COLUMN price_with_vat TO unit_price_with_vat');
    }
}

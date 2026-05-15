<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20241121094752 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE product_stocks SET product_quantity = 0 WHERE product_id IN (SELECT id FROM products WHERE variant_type = \'main\')');
    }
}

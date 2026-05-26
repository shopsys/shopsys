<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20240924082209 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE products ADD product_type VARCHAR(32) DEFAULT NULL');
        $this->sql('UPDATE products SET product_type = \'basic\'');
        $this->sql('ALTER TABLE products ALTER product_type SET NOT NULL');
    }
}

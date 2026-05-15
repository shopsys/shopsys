<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20250312132504 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE promo_code_limit ALTER from_price TYPE NUMERIC(20, 6)');
        $this->sql('ALTER TABLE promo_code_limit ALTER discount TYPE NUMERIC(20, 6)');
    }
}

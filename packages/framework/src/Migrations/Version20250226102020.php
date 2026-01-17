<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250226102020 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE currencies ADD rounding_places_price_without_vat INT NOT NULL DEFAULT 2');
        $this->sql('ALTER TABLE currencies ALTER rounding_places_price_without_vat DROP DEFAULT');
    }
}

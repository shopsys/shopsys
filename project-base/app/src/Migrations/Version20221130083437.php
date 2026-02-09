<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20221130083437 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('UPDATE currencies SET min_fraction_digits = 0, rounding_type = \'integer\' WHERE id = 1');
    }
}

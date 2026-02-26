<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20250326101147 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $inputPriceType = $this->sqlQuery(
            'SELECT value FROM setting_values WHERE name = \'inputPriceType\'',
        )->fetchOne();

        $this->sql('INSERT INTO setting_values (name, domain_id, type, value) VALUES (\'sellingPriceType\', 0, \'integer\', ?)', [$inputPriceType]);
    }

    #[Override]
    public function down(Schema $schema): void
    {
    }
}

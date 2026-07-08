<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260708190000 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO setting_values (name, domain_id, value, type)
            SELECT \'feedCurrencyCodeToContinue\', 0, NULL, \'string\'
            WHERE NOT EXISTS (SELECT 1 FROM setting_values WHERE name = \'feedCurrencyCodeToContinue\' AND domain_id = 0)
        ');
    }
}

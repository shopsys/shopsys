<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20191029210140 extends AbstractMigration
{
    #[Override]
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE currencies ADD rounding_type VARCHAR(15) NOT NULL DEFAULT \'hundredths\'');
        $this->sql('ALTER TABLE currencies ALTER rounding_type DROP DEFAULT');

        $roundingTypeSetting = $this->sqlQuery(
            'SELECT value FROM setting_values WHERE name = \'roundingType\' AND domain_id = 0;',
        )->fetchOne();

        if ($roundingTypeSetting === false) {
            return;
        }

        switch ($roundingTypeSetting) {
            case 1:
                $currencyRoundingType = 'hundredths';

                break;
            case 2:
                $currencyRoundingType = 'fifties';

                break;
            default:
                $currencyRoundingType = 'integer';
        }
        $this->sql(
            'UPDATE currencies SET rounding_type = :currencyRoundingType',
            ['currencyRoundingType' => $currencyRoundingType],
        );
    }
}

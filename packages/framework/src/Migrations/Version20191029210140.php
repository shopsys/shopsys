<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20191029210140 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE currencies ADD rounding_type VARCHAR(15) NOT NULL DEFAULT \'integer\'');
        $this->sql('ALTER TABLE currencies ALTER rounding_type DROP DEFAULT');

        $roundingTypeSetting = $this->sql('SELECT value FROM setting_values WHERE name = \'roundingType\' AND domain_id = 0;')->fetchColumn(0);
        if ($roundingTypeSetting !== false) {
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
            $this->sql('UPDATE currencies SET rounding_type = :currencyRoundingType', ['currencyRoundingType' => $currencyRoundingType]);
        }
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

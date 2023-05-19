<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20181008000001 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql(
            'UPDATE setting_values SET value = 0 WHERE value IS NULL AND type = \'integer\' AND  name = \'defaultAvailabilityInStockId\'',
        );
        $this->sql(
            'UPDATE setting_values SET value = 0 WHERE value IS NULL AND type = \'integer\' AND  name = \'defaultUnitId\'',
        );
        $this->sql('UPDATE setting_values SET type = \'none\' where value IS NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

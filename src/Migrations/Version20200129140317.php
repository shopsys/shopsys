<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200129140317 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('INSERT INTO "setting_values" ("name", "domain_id", "value", "type") VALUES (\'deliveryDayOnStock\', \'1\', \'1\', \'integer\');');
        $this->sql('INSERT INTO "setting_values" ("name", "domain_id", "value", "type") VALUES (\'deliveryDayOnStock\', \'2\', \'1\', \'integer\');');
        $this->sql('INSERT INTO "setting_values" ("name", "domain_id", "value", "type") VALUES (\'transferDaysBetweenStocks\', \'1\', \'1\', \'integer\');');
        $this->sql('INSERT INTO "setting_values" ("name", "domain_id", "value", "type") VALUES (\'transferDaysBetweenStocks\', \'2\', \'1\', \'integer\');');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

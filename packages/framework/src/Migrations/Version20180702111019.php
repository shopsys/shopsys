<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180702111019 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $defaultAvailabilityInStockId = $this->sql(
            'SELECT COUNT(*) FROM setting_values WHERE name = \'defaultAvailabilityInStockId\' AND domain_id = 0;',
        )->fetchOne();
        if ($defaultAvailabilityInStockId > 0) {
            return;
        }
        $this->sql(
            'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'defaultAvailabilityInStockId\', 0, null, \'integer\')',
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

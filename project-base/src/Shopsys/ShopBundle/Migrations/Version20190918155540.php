<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

/**
 * This migration changes the default currencies settings - it should be ran on the clean project-base installation only!
 */
class Version20190918155540 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->sql('UPDATE currencies SET exchange_rate = 0.04 WHERE code = \'CZK\'');
        $this->sql('INSERT INTO currencies (id, name, code, exchange_rate) VALUES (2, \'Euro\', \'EUR\', 1)');
        $this->sql('UPDATE setting_values SET value = 2 WHERE name = \'defaultCurrencyId\'');
        $this->sql('UPDATE setting_values SET value = 2 WHERE name = \'defaultDomainCurrencyId\' AND domain_id = 1');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

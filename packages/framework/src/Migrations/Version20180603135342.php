<?php

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20180603135342 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $currenciesCount = $this->sql('SELECT count(*) FROM currencies')->fetchColumn(0);
        if ($currenciesCount > 0) {
            return;
        }

        $this->sql(
            'INSERT INTO currencies (id, name, code, exchange_rate) VALUES (1, \'Česká koruna\', \'CZK\', 1)'
        );
        $this->sql('INSERT INTO currencies (id, name, code, exchange_rate) VALUES (2, \'Euro\', \'EUR\', 25)');

        $this->sql('ALTER SEQUENCE currencies_id_seq RESTART WITH 3');

        $defaultCurrencyId = $this->sql(
            'SELECT COUNT(*) FROM setting_values WHERE name = \'defaultCurrencyId\' AND domain_id = 0;'
        )->fetchColumn(
            0
        );
        if ($defaultCurrencyId <= 0) {
            $this->sql(
                'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'defaultCurrencyId\', 0, 2, \'integer\')'
            );
        }

        $defaultDomainCurrencyId = $this->sql(
            'SELECT COUNT(*) FROM setting_values WHERE name = \'defaultDomainCurrencyId\' AND domain_id = 1;'
        )->fetchColumn(
            0
        );
        if ($defaultDomainCurrencyId > 0) {
            return;
        }

        $this->sql(
            'INSERT INTO setting_values (name, domain_id, value, type) VALUES (\'defaultDomainCurrencyId\', 1, 2, \'integer\')'
        );
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

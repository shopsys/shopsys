<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

class Version20200903202259 extends AbstractMigration
{
    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function up(Schema $schema): void
    {
        $this->sql('ALTER TABLE transport_prices ADD price_with_currency_amount NUMERIC(20, 6)');
        $this->sql('ALTER TABLE transport_prices ADD price_with_currency_currency CHAR(3)');
        $this->sql('COMMENT ON COLUMN transport_prices.price_with_currency_amount IS \'(DC2Type:big_numbers_decimal)\'');

        $this->sql('UPDATE transport_prices SET price_with_currency_amount=price');

        foreach ([1, 2] as $domainId) {
            $this->sql('
                UPDATE transport_prices
                SET price_with_currency_currency=(
                    SELECT c.code
                    FROM currencies AS c
                    JOIN setting_values AS sv
                    ON sv.value::INTEGER=c.id
                    WHERE sv.name=\'defaultDomainCurrencyId\' AND sv.domain_id = ' . $domainId . '
                )
                WHERE domain_id=' . $domainId . '
            ');
        }

        $this->sql('ALTER TABLE transport_prices ALTER price_with_currency_amount SET NOT NULL');
        $this->sql('ALTER TABLE transport_prices ALTER price_with_currency_currency SET NOT NULL');
    }

    /**
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

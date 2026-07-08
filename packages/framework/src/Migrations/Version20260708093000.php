<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Override;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration;

final class Version20260708093000 extends AbstractMigration implements DomainAwareInterface
{
    use MultidomainMigrationTrait;

    #[Override]
    public function up(Schema $schema): void
    {
        foreach ($this->getAllConfiguredCurrencyCodes() as $currencyCode) {
            $this->sql(
                'INSERT INTO currencies (name, code, exchange_rate, min_fraction_digits, rounding_type, rounding_places_price_without_vat)
                SELECT :name, :code, 1, 2, \'integer\', 2
                WHERE NOT EXISTS (SELECT 1 FROM currencies WHERE code = :existingCode)',
                [
                    'name' => $currencyCode,
                    'code' => $currencyCode,
                    'existingCode' => $currencyCode,
                ],
            );
        }

        $this->sql('DELETE FROM setting_values WHERE name = \'defaultDomainCurrencyId\'');
    }

    /**
     * @return string[]
     */
    private function getAllConfiguredCurrencyCodes(): array
    {
        $currencyCodes = [];

        foreach ($this->getAllDomainConfigs() as $domainConfig) {
            foreach ($domainConfig->getCurrencyCodes() as $currencyCode) {
                $currencyCodes[$currencyCode] = $currencyCode;
            }
        }

        return array_values($currencyCodes);
    }
}

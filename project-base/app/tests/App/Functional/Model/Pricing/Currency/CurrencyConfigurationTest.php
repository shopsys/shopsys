<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Pricing\Currency;

use App\DataFixtures\Demo\CurrencyDataFixture;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyDataCreator;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Exception\DeletingNotAllowedToDeleteCurrencyException;
use Tests\App\Test\TransactionFunctionalTestCase;

class CurrencyConfigurationTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CurrencyDataCreator $currencyDataCreator;

    public function testCurrencyConfiguredForDomainCannotBeDeleted(): void
    {
        $czkCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        $this->expectException(DeletingNotAllowedToDeleteCurrencyException::class);

        $this->currencyFacade->deleteById($czkCurrency->getId());
    }

    public function testAllConfiguredCurrenciesAreNotAllowedToBeDeleted(): void
    {
        $notAllowedToDeleteCurrencyIds = $this->currencyFacade->getNotAllowedToDeleteCurrencyIds();

        foreach ($this->domain->getAll() as $domainConfig) {
            foreach ($domainConfig->getCurrencyCodes() as $currencyCode) {
                $currency = $this->currencyFacade->findByCode($currencyCode);

                self::assertNotNull($currency, sprintf('Currency "%s" from the domain configuration must exist', $currencyCode));
                self::assertContains($currency->getId(), $notAllowedToDeleteCurrencyIds);
            }
        }
    }

    public function testCreateMissingCurrenciesIsIdempotent(): void
    {
        self::assertSame(0, $this->currencyDataCreator->createMissingCurrencies());
    }
}

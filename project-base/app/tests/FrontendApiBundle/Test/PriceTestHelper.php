<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use App\Component\Setting\SettingsProfileApplier;
use App\Model\Product\Product;
use PHPUnit\Framework\Assert;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PriceTestHelper
{
    public function __construct(
        private readonly BasePriceCalculation $basePriceCalculation,
        private readonly CurrencyFacade $currencyFacade,
        private readonly VatFacade $vatFacade,
        private readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        private readonly SettingsProfileApplier $settingsProfileApplier,
    ) {
    }

    /**
     * Porovná ceny v GQL odpovědi s normalizací na minor units
     *
     * @param array<string, mixed> $expectedPrice
     * @param array<string, mixed> $actualGqlPrice
     */
    public function assertPriceEquals(array $expectedPrice, array $actualGqlPrice): void
    {
        $expectedPriceWithVat = Money::create($expectedPrice['priceWithVat']);
        $actualPriceWithVat = Money::create($actualGqlPrice['priceWithVat']);
        
        $expectedPriceWithoutVat = Money::create($expectedPrice['priceWithoutVat']);
        $actualPriceWithoutVat = Money::create($actualGqlPrice['priceWithoutVat']);
        
        $expectedVatAmount = Money::create($expectedPrice['vatAmount']);
        $actualVatAmount = Money::create($actualGqlPrice['vatAmount']);

        Assert::assertEquals(
            $expectedPriceWithVat->getAmount(),
            $actualPriceWithVat->getAmount(),
            'Price with VAT should match'
        );

        Assert::assertEquals(
            $expectedPriceWithoutVat->getAmount(),
            $actualPriceWithoutVat->getAmount(),
            'Price without VAT should match'
        );

        Assert::assertEquals(
            $expectedVatAmount->getAmount(),
            $actualVatAmount->getAmount(),
            'VAT amount should match'
        );
    }

    /**
     * Spočítá očekávané ceny podle aktuálního profilu nastavení
     *
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @param int $quantity
     * @return array<string, string>
     */
    public function getExpectedProductPrice(Product $product, int $domainId, int $quantity = 1): array
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        
        // Získá cenu produktu pro danou pricing group a doménu
        $productPrice = $product->getProductPrice($pricingGroup);
        $vat = $productPrice->getVat();
        
        // Spočítá finální cenu s použitím kalkulačních služeb
        $basePrice = $this->basePriceCalculation->calculateBasePrice(
            $productPrice->getPrice(),
            $pricingGroup->getInternalId(),
            $vat,
            $currency
        );

        // Vynásobí množstvím
        $totalPrice = new Price(
            $basePrice->getPriceWithoutVat()->multiply($quantity),
            $basePrice->getPriceWithVat()->multiply($quantity)
        );
        
        return [
            'priceWithVat' => $totalPrice->getPriceWithVat()->getAmount(),
            'priceWithoutVat' => $totalPrice->getPriceWithoutVat()->getAmount(), 
            'vatAmount' => $totalPrice->getVatAmount()->getAmount(),
        ];
    }

    /**
     * Pomocná metoda pro normalizaci Money objektů na konzistentní formát
     */
    public function normalizeMoneyValue(string $moneyValue): string
    {
        return Money::create($moneyValue)->getAmount();
    }

    /**
     * Zkontroluje, zda jsou ceny v rozsahu tolerance (pro floating point operace)
     *
     * @param array<string, mixed> $expectedPrice
     * @param array<string, mixed> $actualGqlPrice  
     * @param string $tolerance
     */
    public function assertPriceEqualsWithTolerance(array $expectedPrice, array $actualGqlPrice, string $tolerance = '0.01'): void
    {
        $toleranceMoney = Money::create($tolerance);
        
        foreach (['priceWithVat', 'priceWithoutVat', 'vatAmount'] as $field) {
            $expected = Money::create($expectedPrice[$field]);
            $actual = Money::create($actualGqlPrice[$field]);
            $diff = $expected->subtract($actual)->abs();
            
            Assert::assertTrue(
                $diff->isLessThanOrEqualTo($toleranceMoney),
                sprintf(
                    'Price field %s differs too much. Expected: %s, Actual: %s, Diff: %s, Max tolerance: %s',
                    $field,
                    $expected->getAmount(),
                    $actual->getAmount(),
                    $diff->getAmount(),
                    $tolerance
                )
            );
        }
    }
}
<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\ProductPricesMulticurrencyModeProvider;
use Shopsys\FrameworkBundle\Model\Product\Product;

class SpecialPriceFacade
{
    public function __construct(
        protected readonly SpecialPriceFactory $specialPriceFactory,
        protected readonly SpecialPriceRepository $specialPriceRepository,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly ProductPricesMulticurrencyModeProvider $productPricesMulticurrencyModeProvider,
    ) {
    }

    public function findRelevantSpecialPrice(
        Product $product,
        int $domainId,
        PriceInterface $basicPrice,
    ): ?SpecialPrice {
        $targetCurrency = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId);
        $storedPricesCurrency = $this->getStoredPricesCurrency($domainId, $targetCurrency);

        $relevantSpecialPrice = $this->specialPriceRepository->findRelevantSpecialPrice($product, $domainId, $storedPricesCurrency);

        if ($relevantSpecialPrice === null) {
            return null;
        }

        $specialPrice = $this->specialPriceFactory->createWithCalculations(
            $relevantSpecialPrice['validFrom'],
            $relevantSpecialPrice['validTo'],
            $relevantSpecialPrice['priceAmount'],
            $product->getVatForDomain($domainId),
            $relevantSpecialPrice['productListId'],
            $relevantSpecialPrice['productListName'],
            $relevantSpecialPrice['productId'],
            $targetCurrency,
            $storedPricesCurrency,
        );

        if ($specialPrice->price->getPriceWithVat()->isGreaterThanOrEqualTo($basicPrice->getPriceWithVat())) {
            return null;
        }

        return $specialPrice;
    }

    /**
     * @param int[] $variantIds
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice[]
     */
    public function getCurrentAndFutureSpecialPrices(Product $product, int $domainId, array $variantIds = []): array
    {
        $targetCurrency = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId);
        $storedPricesCurrency = $this->getStoredPricesCurrency($domainId, $targetCurrency);

        $specialPrices = $this->specialPriceRepository->getCurrentAndFutureSpecialPrices($product, $domainId, $storedPricesCurrency, $variantIds);

        return array_map(
            function (array $specialPriceData) use ($domainId, $product, $targetCurrency, $storedPricesCurrency) {
                return $this->specialPriceFactory->createWithCalculations(
                    $specialPriceData['validFrom'],
                    $specialPriceData['validTo'],
                    $specialPriceData['priceAmount'],
                    $product->getVatForDomain($domainId),
                    $specialPriceData['productListId'],
                    $specialPriceData['productListName'],
                    $specialPriceData['productId'],
                    $targetCurrency,
                    $storedPricesCurrency,
                );
            },
            $specialPrices,
        );
    }

    /**
     * In the manual multicurrency mode the special prices are stored separately for every enabled currency,
     * in the calculated mode only in the domain default currency (and converted by exchange rate afterwards)
     */
    protected function getStoredPricesCurrency(int $domainId, Currency $targetCurrency): Currency
    {
        if ($this->productPricesMulticurrencyModeProvider->isManualMode()) {
            return $targetCurrency;
        }

        return $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
    }
}

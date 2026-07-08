<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class SpecialPriceFactory
{
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    protected function createInstance(): SpecialPrice
    {
        return new SpecialPrice();
    }

    /**
     * When the stored price amount is in a different currency than the target one, it is converted by exchange rate first,
     * the basic price is converted with the same rate so the "special price is lower than basic price" ordering is preserved
     */
    public function createWithCalculations(
        DateTimeInterface $validFrom,
        DateTimeInterface $validTo,
        Money $specialPriceAmount,
        Vat $vat,
        int $priceListId,
        string $priceListName,
        int $productId,
        Currency $targetCurrency,
        ?Currency $sourceCurrency = null,
    ): SpecialPrice {
        if ($sourceCurrency !== null && $sourceCurrency->getCode() !== $targetCurrency->getCode()) {
            $specialPriceAmount = $specialPriceAmount->multiply(
                (string)$this->currencyFacade->getExchangeRateForCurrencies($sourceCurrency, $targetCurrency),
            );
        }

        $price = $this->basePriceCalculation->calculateRoundedBasePrice(
            $specialPriceAmount,
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $targetCurrency->getRoundingType(),
            $targetCurrency->getRoundingPlacesPriceWithoutVat(),
            $targetCurrency,
        );

        return $this->create(
            $price,
            $validFrom,
            $validTo,
            $priceListId,
            $priceListName,
            $productId,
        );
    }

    public function create(
        PriceInterface $price,
        DateTimeInterface $validFrom,
        DateTimeInterface $validTo,
        int $priceListId,
        string $priceListName,
        int $productId,
    ): SpecialPrice {
        $specialPrice = $this->createInstance();

        $specialPrice->price = $price;
        $specialPrice->validFrom = $validFrom;
        $specialPrice->validTo = $validTo;
        $specialPrice->priceListId = $priceListId;
        $specialPrice->priceListName = $priceListName;
        $specialPrice->productId = $productId;

        return $specialPrice;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class SpecialPriceFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice
     */
    protected function createInstance(): SpecialPrice
    {
        return new SpecialPrice();
    }

    /**
     * @param \DateTimeInterface $validFrom
     * @param \DateTimeInterface $validTo
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $specialPriceAmount
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $priceListId
     * @param string $priceListName
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice
     */
    public function createWithCalculations(
        DateTimeInterface $validFrom,
        DateTimeInterface $validTo,
        Money $specialPriceAmount,
        int $domainId,
        Vat $vat,
        int $priceListId,
        string $priceListName,
        int $productId,
    ): SpecialPrice {
        $price = $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $specialPriceAmount,
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId),
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param \DateTimeInterface $validFrom
     * @param \DateTimeInterface $validTo
     * @param int $priceListId
     * @param string $priceListName
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice
     */
    public function create(
        Price $price,
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

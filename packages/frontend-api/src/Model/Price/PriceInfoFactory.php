<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use DateTimeImmutable;
use DateTimeInterface;
use LogicException;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;

class PriceInfoFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $basicProductPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice|null $specialPrice
     * @return \Shopsys\FrontendApiBundle\Model\Price\PriceInfo
     */
    public function create(
        ProductPrice $basicProductPrice,
        ?SpecialPrice $specialPrice,
    ): PriceInfo {
        $priceInfo = new PriceInfo();
        $priceInfo->basicPrice = $basicProductPrice;
        $priceInfo->isPriceFrom = $basicProductPrice->isPriceFrom();

        if ($specialPrice === null) {
            $priceInfo->setSellingPrice($basicProductPrice);

            return $priceInfo;
        }

        $priceInfo->nextPriceChange = $this->determineNextPriceChange($specialPrice);

        if (!$specialPrice->isFuturePrice()) {
            $priceInfo->setSellingPrice($specialPrice->price);
            $priceInfo->percentageDiscount = $this->calculatePercentageDiscount($basicProductPrice->getPriceWithVat(), $specialPrice->price->getPriceWithVat());
        } else {
            $priceInfo->setSellingPrice($basicProductPrice);
        }

        return $priceInfo;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Price\PriceInfo
     */
    public function createHiddenPriceInfo(): PriceInfo
    {
        return $this->create(
            ProductPrice::createHiddenProductPrice(),
            null,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice $specialPrice
     * @return \DateTimeInterface
     */
    protected function determineNextPriceChange(SpecialPrice $specialPrice): DateTimeInterface
    {
        $now = new DateTimeImmutable();

        $futureDates = [];

        if ($specialPrice->validFrom > $now) {
            $futureDates[] = $specialPrice->validFrom;
        }

        if ($specialPrice->validTo > $now) {
            $futureDates[] = $specialPrice->validTo;
        }

        if (count($futureDates) === 0) {
            throw new LogicException('Special price was selected, but the validity is in the past. Check the implementation of the special price selection algorithm.');
        }

        return min($futureDates);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basicPriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $specialPriceWithVat
     * @return float
     */
    protected function calculatePercentageDiscount(
        Money $basicPriceWithVat,
        Money $specialPriceWithVat,
    ): float {
        $floatDiscount = $basicPriceWithVat
            ->subtract($specialPriceWithVat)
            ->divide($basicPriceWithVat->getAmount(), 6)
            ->multiply(100);

        return floor((float)$floatDiscount->getAmount());
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use DateTimeInterface;
use LogicException;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;

class PriceInfoFactory
{
    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    public function create(
        ProductPriceInterface $basicProductPrice,
        ?SpecialPrice $specialPrice,
    ): PriceInfo {
        $priceInfo = new PriceInfo();
        $price = $basicProductPrice->getPrice();
        $priceInfo->basicPrice = $price;
        $priceInfo->isPriceFrom = $basicProductPrice->isPriceFrom();

        if ($specialPrice === null) {
            $priceInfo->setSellingPrice($price);

            return $priceInfo;
        }

        $priceInfo->nextPriceChange = $this->determineNextPriceChange($specialPrice);

        if (!$specialPrice->isFuturePrice()) {
            $priceInfo->setSellingPrice($specialPrice->price);
            $priceInfo->percentageDiscount = $this->calculatePercentageDiscount($price->getPriceWithVat(), $specialPrice->price->getPriceWithVat());
        } else {
            $priceInfo->setSellingPrice($price);
        }

        return $priceInfo;
    }

    public function createHiddenPriceInfo(PricingGroup $pricingGroup): PriceInfo
    {
        return $this->create(
            ProductPrice::createHiddenProductPrice($pricingGroup),
            null,
        );
    }

    protected function determineNextPriceChange(SpecialPrice $specialPrice): DateTimeInterface
    {
        $now = $this->clock->now();

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

    protected function calculatePercentageDiscount(
        Money $basicPriceWithVat,
        Money $specialPriceWithVat,
    ): float {
        $floatDiscount = $basicPriceWithVat
            ->subtract($specialPriceWithVat)
            ->divide($basicPriceWithVat->getAmount(), 6)
            ->multiply(100);

        return max(1, floor((float)$floatDiscount->getAmount()));
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product;

class SpecialPriceFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFactory $specialPriceFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceRepository $specialPriceRepository
     */
    public function __construct(
        protected readonly SpecialPriceFactory $specialPriceFactory,
        protected readonly SpecialPriceRepository $specialPriceRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $basicPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice|null
     */
    public function getEffectiveSpecialPrice(Product $product, int $domainId, Price $basicPrice): ?SpecialPrice
    {
        $effectiveSpecialPrice = $this->specialPriceRepository->getEffectiveSpecialPrice($product, $domainId);

        if ($effectiveSpecialPrice === null) {
            return null;
        }

        $specialPrice = $this->specialPriceFactory->createWithCalculations(
            $effectiveSpecialPrice['validFrom'],
            $effectiveSpecialPrice['validTo'],
            $effectiveSpecialPrice['priceAmount'],
            $domainId,
            $product->getVatForDomain($domainId),
            $effectiveSpecialPrice['productListId'],
            $effectiveSpecialPrice['productListName'],
            $effectiveSpecialPrice['productId'],
        );

        if ($specialPrice->price->getPriceWithVat()->isGreaterThanOrEqualTo($basicPrice->getPriceWithVat())) {
            return null;
        }

        return $specialPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param int[] $variantIds
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice[]
     */
    public function getCurrentAndFutureSpecialPrices(Product $product, int $domainId, array $variantIds = []): array
    {
        $specialPrices = $this->specialPriceRepository->getCurrentAndFutureSpecialPrices($product, $domainId, $variantIds);

        return array_map(
            function (array $specialPriceData) use ($domainId, $product) {
                return $this->specialPriceFactory->createWithCalculations(
                    $specialPriceData['validFrom'],
                    $specialPriceData['validTo'],
                    $specialPriceData['priceAmount'],
                    $domainId,
                    $product->getVatForDomain($domainId),
                    $specialPriceData['productListId'],
                    $specialPriceData['productListName'],
                    $specialPriceData['productId'],
                );
            },
            $specialPrices,
        );
    }
}

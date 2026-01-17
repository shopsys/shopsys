<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class SpecialPriceFacade
{
    public function __construct(
        protected readonly SpecialPriceFactory $specialPriceFactory,
        protected readonly SpecialPriceRepository $specialPriceRepository,
    ) {
    }

    public function findRelevantSpecialPrice(
        Product $product,
        int $domainId,
        PriceInterface $basicPrice,
    ): ?SpecialPrice {
        $relevantSpecialPrice = $this->specialPriceRepository->findRelevantSpecialPrice($product, $domainId);

        if ($relevantSpecialPrice === null) {
            return null;
        }

        $specialPrice = $this->specialPriceFactory->createWithCalculations(
            $relevantSpecialPrice['validFrom'],
            $relevantSpecialPrice['validTo'],
            $relevantSpecialPrice['priceAmount'],
            $domainId,
            $product->getVatForDomain($domainId),
            $relevantSpecialPrice['productListId'],
            $relevantSpecialPrice['productListName'],
            $relevantSpecialPrice['productId'],
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

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
                    $specialPriceData['productId'],
                );
            },
            $specialPrices,
        );
    }
}

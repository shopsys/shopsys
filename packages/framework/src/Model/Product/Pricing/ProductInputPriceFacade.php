<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductInputPriceFacade
{
    public function __construct(
        protected readonly ProductManualInputPriceRepository $productManualInputPriceRepository,
    ) {
    }

    /**
     * @return array<int, array<int, \Shopsys\FrameworkBundle\Component\Money\Money|null>>
     */
    public function getManualInputPricesDataIndexedByDomainIdAndPricingGroupId(Product $product): array
    {
        $manualInputPricesDataByPricingGroupId = [];

        $manualInputPrices = $this->productManualInputPriceRepository->getByProduct($product);

        foreach ($manualInputPrices as $manualInputPrice) {
            $pricingGroup = $manualInputPrice->getPricingGroup();
            $manualInputPricesDataByPricingGroupId[$pricingGroup->getDomainId()][$pricingGroup->getId()] = $manualInputPrice->getInputPrice();
        }

        return $manualInputPricesDataByPricingGroupId;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoryFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     */
    public function __construct(protected readonly ProductAccessoryRepository $productAccessoryRepository)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedAccessories(
        Product $product,
        int $domainId,
        PricingGroup $pricingGroup,
        ?int $limit = null,
    ) {
        return $this->productAccessoryRepository->getOfferedAccessories($product, $domainId, $pricingGroup, $limit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory[]
     */
    public function getAllAccessories(Product $product): array
    {
        return $this->productAccessoryRepository->getAllByProduct($product);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoryFacade
{
    public function __construct(protected readonly ProductAccessoryRepository $productAccessoryRepository)
    {
    }

    /**
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory[]
     */
    public function getAllAccessories(Product $product): array
    {
        return $this->productAccessoryRepository->getAllByProduct($product);
    }
}

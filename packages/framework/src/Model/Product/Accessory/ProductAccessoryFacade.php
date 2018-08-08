<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoryFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository
     */
    protected $productAccessoryRepository;

    public function __construct(ProductAccessoryRepository $productAccessoryRepository)
    {
        $this->productAccessoryRepository = $productAccessoryRepository;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getTopOfferedAccessories(Product $product, int $domainId, PricingGroup $pricingGroup, int $limit): array
    {
        return $this->productAccessoryRepository->getTopOfferedAccessories($product, $domainId, $pricingGroup, $limit);
    }
}

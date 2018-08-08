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
     * @param int $domainId
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getTopOfferedAccessories(Product $product, $domainId, PricingGroup $pricingGroup, $limit)
    {
        return $this->productAccessoryRepository->getTopOfferedAccessories($product, $domainId, $pricingGroup, $limit);
    }
}

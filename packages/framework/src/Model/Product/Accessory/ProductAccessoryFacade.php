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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     */
    public function __construct(ProductAccessoryRepository $productAccessoryRepository)
    {
        $this->productAccessoryRepository = $productAccessoryRepository;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getTopOfferedAccessories(Product $product, int $domainId, PricingGroup $pricingGroup, ?int $limit): array
    {
        return $this->productAccessoryRepository->getTopOfferedAccessories($product, $domainId, $pricingGroup, $limit);
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

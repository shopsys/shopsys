<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
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
     * @return array
     */
    public function getAllAccessories(Product $product, int $domainId, PricingGroup $pricingGroup): array
    {
        return $this->productAccessoryRepository->getAllOfferedAccessoriesByProduct(
            $product,
            $domainId,
            $pricingGroup
        );
    }
}

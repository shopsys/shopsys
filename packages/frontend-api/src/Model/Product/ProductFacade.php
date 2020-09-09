<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductFacade
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getSellableByUuid(string $uuid, int $domainId, PricingGroup $pricingGroup): Product
    {
        return $this->productRepository->getSellableByUuid($uuid, $domainId, $pricingGroup);
    }
}

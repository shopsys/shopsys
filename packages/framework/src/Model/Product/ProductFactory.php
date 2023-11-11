<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation;

class ProductFactory implements ProductFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityCalculation $productAvailabilityCalculation
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly ProductAvailabilityCalculation $productAvailabilityCalculation,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductData $data): Product
    {
        $classData = $this->entityNameResolver->resolve(Product::class);

        $product = $classData::create($data);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createMainVariant(ProductData $data, Product $mainProduct, array $variants): Product
    {
        $variants[] = $mainProduct;

        $classData = $this->entityNameResolver->resolve(Product::class);

        $mainVariant = $classData::createMainVariant($data, $variants);

        return $mainVariant;
    }
}

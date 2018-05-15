<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;

class ProductFactory implements ProductFactoryInterface
{

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $defaultInStockAvailability
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function create(ProductData $data, Availability $defaultInStockAvailability): Product
    {
        return Product::create($data, $defaultInStockAvailability);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $data
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $defaultInStockAvailability
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function createMainVariant(ProductData $data, Availability $defaultInStockAvailability, array $variants): Product
    {
        return Product::createMainVariant($data, $defaultInStockAvailability, $variants);
    }
}

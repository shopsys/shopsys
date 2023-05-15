<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Doctrine\__fixtures;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity
 */
class Product extends BaseProduct
{
    /**
     * @param \Tests\FrameworkBundle\Unit\Component\Doctrine\__fixtures\ProductData $productData
     * @param \Tests\FrameworkBundle\Unit\Component\Doctrine\__fixtures\Product[]|null $variants
     */
    protected function __construct(BaseProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @param \Tests\FrameworkBundle\Unit\Component\Doctrine\__fixtures\ProductData $productData
     */
    public function edit(
        array $productCategoryDomains,
        BaseProductData $productData,
    ) {
        parent::edit($productCategoryDomains, $productData);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Product;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @ORM\Table(name="products")
 * @ORM\Entity
 * @property \Shopsys\ShopBundle\Model\Product\Brand\Brand|null $brand
 * @property \Shopsys\ShopBundle\Model\Product\Product[]|\Doctrine\Common\Collections\Collection $variants
 * @property \Shopsys\ShopBundle\Model\Product\Product|null $mainVariant
 * @method static \Shopsys\ShopBundle\Model\Product\Product create($productData)
 * @method static \Shopsys\ShopBundle\Model\Product\Product createMainVariant($productData, $variants)
 * @method \Shopsys\ShopBundle\Model\Category\Category[][] getCategoriesIndexedByDomainId()
 * @method \Shopsys\ShopBundle\Model\Product\Brand\Brand|null getBrand()
 * @method \Shopsys\ShopBundle\Model\Product\Product getMainVariant()
 * @method \Shopsys\ShopBundle\Model\Product\Product[] getVariants()
 */
class Product extends BaseProduct
{
    /**
     * @param \Shopsys\ShopBundle\Model\Product\ProductData $productData
     * @param \Shopsys\ShopBundle\Model\Product\Product[]|null $variants
     */
    protected function __construct(BaseProductData $productData, ?array $variants = null)
    {
        parent::__construct($productData, $variants);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain[] $productCategoryDomains
     * @param \Shopsys\ShopBundle\Model\Product\ProductData $productData
     */
    public function edit(
        array $productCategoryDomains,
        BaseProductData $productData
    ) {
        parent::edit($productCategoryDomains, $productData);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductData as BaseProductData;

/**
 * @property \Shopsys\ShopBundle\Model\Category\Category[][] $categoriesByDomainId
 * @property \Shopsys\ShopBundle\Model\Product\Brand\Brand|null $brand
 * @property \Shopsys\ShopBundle\Model\Product\Product[] $accessories
 * @property \Shopsys\ShopBundle\Model\Product\Product[] $variants
 */
class ProductData extends BaseProductData
{
}

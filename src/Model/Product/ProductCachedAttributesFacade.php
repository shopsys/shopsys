<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade as BaseProductCachedAttributesFacade;

/**
 * @property \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
 * @method __construct(\App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization)
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null getProductSellingPrice(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValues(\App\Model\Product\Product $product)
 */
class ProductCachedAttributesFacade extends BaseProductCachedAttributesFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice[]
     */
    protected $nonSellingPricesByProductId = [];

    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getProductNonSellingPrice(Product $product): ProductPrice
    {
        if (array_key_exists($product->getId(), $this->nonSellingPricesByProductId)) {
            return $this->sellingPricesByProductId[$product->getId()];
        }

        $productPrice = $this->productPriceCalculationForCustomerUser->calculateNonSellingPriceForCurrentUser($product);
        $this->sellingPricesByProductId[$product->getId()] = $productPrice;

        return $productPrice;
    }
}

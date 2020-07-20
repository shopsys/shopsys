<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade as BaseProductCachedAttributesFacade;

/**
 * @property \App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
 * @method \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null getProductSellingPrice(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValues(\App\Model\Product\Product $product)
 * @property \App\Model\Product\Parameter\ParameterRepository $parameterRepository
 * @method __construct(\App\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser, \App\Model\Product\Parameter\ParameterRepository $parameterRepository, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization)
 */
class ProductCachedAttributesFacade extends BaseProductCachedAttributesFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice[]
     */
    protected array $nonSellingPricesByProductId = [];

    /**
     * @var \App\Model\Product\Parameter\ParameterValuesViewData[][]
     */
    private array $parameterValuesViewsDataByProductId = [];

    /**
     * @param \App\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getProductNonSellingPrice(Product $product): ProductPrice
    {
        if (array_key_exists($product->getId(), $this->nonSellingPricesByProductId)) {
            return $this->sellingPricesByProductId[$product->getId()];
        }

        $productPrice = $this->productPriceCalculationForCustomerUser->calculateNonSellingPriceForCurrentUserAndDomainId($product);
        $this->sellingPricesByProductId[$product->getId()] = $productPrice;

        return $productPrice;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public function getParameterValuesViewsData(Product $product): array
    {
        $productId = $product->getId();
        if (array_key_exists($productId, $this->parameterValuesViewsDataByProductId) === false) {
            $locale = $this->localization->getLocale();
            $this->parameterValuesViewsDataByProductId[$productId] = $this->parameterRepository->getParameterValuesViewDataByProduct($product, $locale);
        }

        return $this->parameterValuesViewsDataByProductId[$productId];
    }
}

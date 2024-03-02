<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;

class ProductCachedAttributesFacade
{
    protected const SELLING_PRICES_CACHE_NAMESPACE = 'sellingPricesByProductId';
    protected const PARAMETER_VALUES_CACHE_NAMESPACE = 'parameterValuesByProductId';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\LocalCache\LocalCacheFacade $localCacheFacade
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Localization $localization,
        protected readonly LocalCacheFacade $localCacheFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getProductSellingPrice(Product $product)
    {
        $key = (string)$product->getId();

        if ($this->localCacheFacade->hasItem(static::SELLING_PRICES_CACHE_NAMESPACE, $key)) {
            return $this->localCacheFacade->getItem(static::SELLING_PRICES_CACHE_NAMESPACE, $key);
        }

        try {
            $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser($product);
        } catch (MainVariantPriceCalculationException $ex) {
            $productPrice = null;
        }
        $this->localCacheFacade->save(static::SELLING_PRICES_CACHE_NAMESPACE, $key, $productPrice);

        return $productPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string|null $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product, ?string $locale = null)
    {
        $key = (string)$product->getId();

        if ($this->localCacheFacade->hasItem(static::PARAMETER_VALUES_CACHE_NAMESPACE, $key)) {
            return $this->localCacheFacade->getItem(static::PARAMETER_VALUES_CACHE_NAMESPACE, $key);
        }
        $locale = $locale ?? $this->localization->getLocale();

        $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByName(
            $product,
            $locale,
        );

        foreach ($productParameterValues as $index => $productParameterValue) {
            $parameter = $productParameterValue->getParameter();

            if ($parameter->getName($locale) === null
                || $productParameterValue->getValue()->getLocale() !== $locale
            ) {
                unset($productParameterValues[$index]);
            }
        }
        $this->localCacheFacade->save(static::PARAMETER_VALUES_CACHE_NAMESPACE, $key, $productParameterValues);

        return $productParameterValues;
    }
}

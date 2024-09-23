<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;

class ProductCachedAttributesFacade
{
    protected const string SELLING_PRICES_CACHE_NAMESPACE = 'sellingPricesByProductId';
    protected const string PARAMETER_VALUES_CACHE_NAMESPACE = 'parameterValuesByProductId';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository $parameterRepository
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Localization $localization,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice|null
     */
    public function getProductSellingPrice(Product $product)
    {
        $key = (string)$product->getId();

        if ($this->inMemoryCache->hasItem(static::SELLING_PRICES_CACHE_NAMESPACE, $key)) {
            return $this->inMemoryCache->getItem(static::SELLING_PRICES_CACHE_NAMESPACE, $key);
        }

        try {
            $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser($product);
        } catch (MainVariantPriceCalculationException $ex) {
            $productPrice = null;
        }
        $this->inMemoryCache->save(static::SELLING_PRICES_CACHE_NAMESPACE, $productPrice, $key);

        return $productPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string|null $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product, ?string $locale = null)
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PARAMETER_VALUES_CACHE_NAMESPACE,
            function () use ($product, $locale) {
                $locale = $locale ?? $this->localization->getLocale();

                $productParameterValues = $this->parameterRepository->getProductParameterValuesByProductSortedByOrderingPriorityAndName(
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

                return $productParameterValues;
            },
            $product->getId(),
        );
    }
}

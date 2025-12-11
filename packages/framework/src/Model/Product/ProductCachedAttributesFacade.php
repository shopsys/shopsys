<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;

class ProductCachedAttributesFacade
{
    protected const string BASIC_PRICES_CACHE_NAMESPACE = 'basicPricesByProductId';
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface|null
     */
    public function getProductBasicPrice(Product $product): ?ProductPriceInterface
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::BASIC_PRICES_CACHE_NAMESPACE,
            function () use ($product) {
                try {
                    return $this->productPriceCalculationForCustomerUser->calculateBasicPriceForCurrentUser($product);
                } catch (MainVariantPriceCalculationException) {
                    return null;
                }
            },
            $product->getId(),
        );
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
                $locale = $locale ?? $this->localization->getCurrentLocaleForTranslatableEntities();

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

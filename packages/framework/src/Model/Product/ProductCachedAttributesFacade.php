<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;

class ProductCachedAttributesFacade
{
    protected const string PARAMETER_VALUES_CACHE_NAMESPACE = 'parameterValuesByProductId';

    public function __construct(
        protected readonly ParameterRepository $parameterRepository,
        protected readonly Localization $localization,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValues(Product $product, ?string $locale = null): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::PARAMETER_VALUES_CACHE_NAMESPACE,
            function () use ($product, $locale): array {
                $locale ??= $this->localization->getCurrentLocaleForTranslatableEntities();

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

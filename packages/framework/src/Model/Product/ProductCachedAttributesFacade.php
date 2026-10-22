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
        $locale ??= $this->localization->getCurrentLocaleForTranslatableEntities();

        return $this->inMemoryCache->getOrSaveValue(
            static::PARAMETER_VALUES_CACHE_NAMESPACE,
            function () use ($product, $locale): array {
                return $this->parameterRepository->getProductParameterValuesByProductSortedByOrderingPriorityAndName(
                    $product,
                    $locale,
                );
            },
            $product->getId(),
            $locale,
        );
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterToLuigisBoxFilterMapper
{
    protected const string FILTER_OR = 'f';
    protected const string FILTER_AND = 'f_must';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @return array
     */
    public function mapForSearch(ProductFilterData $productFilterData, Domain $domain): array
    {
        $luigisBoxFilter = [
            self::FILTER_AND => [],
            self::FILTER_OR => [],
        ];

        $luigisBoxFilter = $this->mapPrice($productFilterData, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapAvailability($productFilterData, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapFlags($productFilterData, $luigisBoxFilter, $domain->getLocale());
        $luigisBoxFilter = $this->mapBrands($productFilterData, $luigisBoxFilter);

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapPrice(ProductFilterData $productFilterData, array $luigisBoxFilter): array
    {
        if ($productFilterData->minimalPrice !== null || $productFilterData->maximalPrice !== null) {
            $priceFrom = $productFilterData->minimalPrice === null ? '' : $productFilterData->minimalPrice->getAmount();
            $priceTo = $productFilterData->maximalPrice === null ? '' : $productFilterData->maximalPrice->getAmount();

            $luigisBoxFilter[self::FILTER_AND][] = 'price:' . $priceFrom . '|' . $priceTo;
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapAvailability(
        ProductFilterData $productFilterData,
        array $luigisBoxFilter,
    ): array {
        if ($productFilterData->inStock === true) {
            $luigisBoxFilter[self::FILTER_AND][] = 'availability:1';
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @param string $locale
     * @return array
     */
    protected function mapFlags(ProductFilterData $productFilterData, array $luigisBoxFilter, string $locale): array
    {
        if (count($productFilterData->flags) > 0) {
            foreach ($productFilterData->flags as $flag) {
                $luigisBoxFilter[self::FILTER_OR][] = 'labels:' . $flag->getName($locale);
            }
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @return array
     */
    protected function mapBrands(ProductFilterData $productFilterData, array $luigisBoxFilter): array
    {
        if (count($productFilterData->brands) > 0) {
            foreach ($productFilterData->brands as $brand) {
                $luigisBoxFilter[self::FILTER_OR][] = 'brand:' . $brand->getName();
            }
        }

        return $luigisBoxFilter;
    }
}

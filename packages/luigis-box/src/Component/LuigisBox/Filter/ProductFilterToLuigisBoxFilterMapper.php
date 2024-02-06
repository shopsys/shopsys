<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterToLuigisBoxFilterMapper
{
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
        $luigisBoxFilter = [];

        $luigisBoxFilter = $this->mapPrice($productFilterData, $luigisBoxFilter);
        $luigisBoxFilter = $this->mapAvailability($productFilterData, $luigisBoxFilter, $domain->getLocale());
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
        if ($productFilterData->minimalPrice !== null) {
            $luigisBoxFilter['must'][] = [
                'type' => 'customRule',
                'fields' => [
                    'price',
                    '$gte',
                    'value',
                    $productFilterData->minimalPrice->getAmount(),
                ],
            ];
        }

        if ($productFilterData->maximalPrice !== null) {
            $luigisBoxFilter['must'][] = [
                'type' => 'customRule',
                'fields' => [
                    'price',
                    '$lte',
                    'value',
                    $productFilterData->maximalPrice->getAmount(),
                ],
            ];
        }

        return $luigisBoxFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $luigisBoxFilter
     * @param string $locale
     * @return array
     */
    protected function mapAvailability(
        ProductFilterData $productFilterData,
        array $luigisBoxFilter,
        string $locale,
    ): array {
        if ($productFilterData->inStock === true) {
            $luigisBoxFilter['must'] = [
                [
                    'type' => 'customRule',
                    'fields' => [
                        'availability',
                        '$in',
                        'value',
                        $this->productAvailabilityFacade->getOnStockText($locale),
                    ],
                ],
            ];
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
                $luigisBoxFilter['must'][] = [
                    'type' => 'customRule',
                    'fields' => [
                        'tag' . TransformString::safeFilename($flag->getName($locale)),
                        '$in',
                        'value',
                        'true',
                    ],
                ];
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
            $luigisBoxFilter['must'] = [
                [
                    'type' => 'customRule',
                    'fields' => [
                        'brand',
                        '$in',
                        'value',
                        '"' . implode('","', array_map(static fn (Brand $brand) => $brand->getName(), $productFilterData->brands)) . '"',
                    ],
                ],
            ];
        }

        return $luigisBoxFilter;
    }
}

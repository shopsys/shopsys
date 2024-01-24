<?php

declare(strict_types=1);

namespace Shopsys\PersooBundle\Component\Persoo\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterToPersooFilterMapper
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
        $persooFilter = [];

        $persooFilter = $this->mapPrice($productFilterData, $persooFilter);
        $persooFilter = $this->mapAvailability($productFilterData, $persooFilter, $domain->getLocale());
        $persooFilter = $this->mapFlags($productFilterData, $persooFilter, $domain->getLocale());
        $persooFilter = $this->mapBrands($productFilterData, $persooFilter);

        return $persooFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $persooFilter
     * @return array
     */
    protected function mapPrice(ProductFilterData $productFilterData, array $persooFilter): array
    {
        if ($productFilterData->minimalPrice !== null) {
            $persooFilter['must'][] = [
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
            $persooFilter['must'][] = [
                'type' => 'customRule',
                'fields' => [
                    'price',
                    '$lte',
                    'value',
                    $productFilterData->maximalPrice->getAmount(),
                ],
            ];
        }

        return $persooFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $persooFilter
     * @param string $locale
     * @return array
     */
    protected function mapAvailability(
        ProductFilterData $productFilterData,
        array $persooFilter,
        string $locale,
    ): array {
        if ($productFilterData->inStock === true) {
            $persooFilter['must'] = [
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

        return $persooFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $persooFilter
     * @param string $locale
     * @return array
     */
    protected function mapFlags(ProductFilterData $productFilterData, array $persooFilter, string $locale): array
    {
        if (count($productFilterData->flags) > 0) {
            foreach ($productFilterData->flags as $flag) {
                $persooFilter['must'][] = [
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

        return $persooFilter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param array $persooFilter
     * @return array
     */
    protected function mapBrands(ProductFilterData $productFilterData, array $persooFilter): array
    {
        if (count($productFilterData->brands) > 0) {
            $persooFilter['must'] = [
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

        return $persooFilter;
    }
}

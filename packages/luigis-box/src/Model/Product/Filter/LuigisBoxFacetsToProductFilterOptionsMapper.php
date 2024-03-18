<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory;
use Shopsys\LuigisBoxBundle\Model\Brand\BrandRepository;
use Shopsys\LuigisBoxBundle\Model\Flag\FlagRepository;

class LuigisBoxFacetsToProductFilterOptionsMapper
{
    public const string FACET_AVAILABILITY = 'availability_rank_text';
    public const string FACET_BRAND = 'brand';
    public const string FACET_LABELS = 'labels';
    public const string FACET_PRICE = 'price_amount';
    public const array PRODUCT_FACET_NAMES = [
        self::FACET_AVAILABILITY,
        self::FACET_BRAND,
        self::FACET_LABELS,
        self::FACET_PRICE,
    ];

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Brand\BrandRepository $brandRepository
     * @param \Shopsys\LuigisBoxBundle\Model\Flag\FlagRepository $flagRepository
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsFactory $productFilterOptionsFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory $productFilterConfigFactory
     */
    public function __construct(
        protected readonly BrandRepository $brandRepository,
        protected readonly FlagRepository $flagRepository,
        protected readonly ProductFilterOptionsFactory $productFilterOptionsFactory,
        protected readonly ProductFilterConfigFactory $productFilterConfigFactory,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $luigisBoxFacets
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptions
     */
    public function map(array $luigisBoxFacets, ProductFilterData $productFilterData): ProductFilterOptions
    {
        $brands = [];
        $flags = [];
        $priceRange = new PriceRange(Money::zero(), Money::zero());

        $productFilterCountData = new ProductFilterCountData();

        foreach ($luigisBoxFacets as $facetData) {
            if ($facetData['name'] === self::FACET_AVAILABILITY) {
                $productFilterCountData->countInStock = $facetData['values'][0]['hits_count'] ?? 0;
            }

            if ($facetData['name'] === self::FACET_BRAND) {
                $brandCountsByName = $this->mapValuesToCountsByName($facetData['values']);
                $brands = $this->brandRepository->getBrandsByNames(array_keys($brandCountsByName));

                foreach ($brands as $brand) {
                    $productFilterCountData->countByBrandId[$brand->getId()] = $brandCountsByName[$brand->getName()];
                }
            }

            if ($facetData['name'] === self::FACET_LABELS) {
                $flagCountsByName = $this->mapValuesToCountsByName($facetData['values']);
                $flags = $this->flagRepository->getFlagsByNames(array_keys($flagCountsByName));

                foreach ($flags as $flag) {
                    $productFilterCountData->countByFlagId[$flag->getId()] = $flagCountsByName[$flag->getName()];
                }
            }

            if ($facetData['name'] !== self::FACET_PRICE) {
                continue;
            }

            $minPrice = 0;
            $maxPrice = 0;

            foreach ($facetData['values'] as $facetValue) {
                [$minValue, $maxValue] = explode('|', $facetValue['value']);

                if ($minValue < $minPrice || $minPrice === 0) {
                    $minPrice = $minValue;
                }

                if ($maxValue > $maxPrice) {
                    $maxPrice = $maxValue;
                }
            }

            $priceRange = new PriceRange(Money::create($minPrice), Money::create($maxPrice));
        }

        $productFilterConfig = $this->productFilterConfigFactory->create([], $flags, $brands, $priceRange);

        return $this->productFilterOptionsFactory->createProductFilterOptions(
            $productFilterConfig,
            $productFilterCountData,
            $productFilterData,
        );
    }

    /**
     * @param array<string, mixed> $facetValues
     * @return array
     */
    protected function mapValuesToCountsByName(array $facetValues): array
    {
        $valuesToCountsByName = [];

        foreach ($facetValues as $facetValue) {
            $valuesToCountsByName[$facetValue['value']] = $facetValue['hits_count'];
        }

        return $valuesToCountsByName;
    }
}

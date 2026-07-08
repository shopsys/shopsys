<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;

class ProductFilterConfigIdsDataFactory
{
    public function __construct(
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly Domain $domain,
    ) {
    }

    public function createFromElasticsearchAggregationResult(
        array $aggregationElasticsearchResult,
    ): ProductFilterConfigIdsData {
        return new ProductFilterConfigIdsData(
            $this->extractParameterValueIdsByParameterId($aggregationElasticsearchResult),
            $this->extractFlagIds($aggregationElasticsearchResult),
            $this->extractBrandIds($aggregationElasticsearchResult),
            $this->extractPriceRange($aggregationElasticsearchResult),
        );
    }

    /**
     * @return int[]
     */
    protected function extractBrandIds(array $aggregationResult): array
    {
        $brandsData = $aggregationResult['brands']['buckets'];

        if (count($brandsData) === 0) {
            return [];
        }

        return array_map(function (array $data) {
            return $data['key'];
        }, $brandsData);
    }

    /**
     * @return int[]
     */
    protected function extractFlagIds(array $aggregationResult): array
    {
        $flagsData = $aggregationResult['flags']['buckets'];

        if (count($flagsData) === 0) {
            return [];
        }

        return array_map(function (array $data) {
            return $data['key'];
        }, $flagsData);
    }

    protected function extractPriceRange(array $aggregationResult): PriceRange
    {
        $pricesData = $aggregationResult['prices'];

        $minPrice = Money::create((string)($pricesData['min_price']['value'] ?? 0));
        $maxPrice = Money::create((string)($pricesData['max_price']['value'] ?? 0));

        $currentCurrency = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($this->domain->getId());
        $minPrice = $minPrice->round($currentCurrency->getMinFractionDigits());
        $maxPrice = $maxPrice->round($currentCurrency->getMinFractionDigits());

        return new PriceRange($minPrice, $maxPrice);
    }

    protected function extractParameterValueIdsByParameterId(array $aggregationResult): array
    {
        if (!array_key_exists('parameters', $aggregationResult)) {
            return [];
        }

        $parametersData = $aggregationResult['parameters']['by_parameters']['buckets'];

        $parameterValueIdsIndexedByParameterId = [];

        foreach ($parametersData as $parameter) {
            $parameterValueIdsIndexedByParameterId[$parameter['key']] = array_map(function ($parameterValue) {
                return $parameterValue['key'];
            }, $parameter['by_value']['buckets']);
        }

        if (count($parameterValueIdsIndexedByParameterId) === 0) {
            return [];
        }

        return $parameterValueIdsIndexedByParameterId;
    }
}

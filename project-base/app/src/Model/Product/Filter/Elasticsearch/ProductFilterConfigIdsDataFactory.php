<?php

declare(strict_types=1);

namespace App\Model\Product\Filter\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange;

class ProductFilterConfigIdsDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly CurrencyFacade $currencyFacade,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param array $aggregationElasticsearchResult
     * @return \App\Model\Product\Filter\Elasticsearch\ProductFilterConfigIdsData
     */
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
     * @param array $aggregationResult
     * @return int[]
     */
    private function extractBrandIds(array $aggregationResult): array
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
     * @param array $aggregationResult
     * @return int[]
     */
    private function extractFlagIds(array $aggregationResult): array
    {
        $flagsData = $aggregationResult['flags']['buckets'];

        if (count($flagsData) === 0) {
            return [];
        }

        return array_map(function (array $data) {
            return $data['key'];
        }, $flagsData);
    }

    /**
     * @param array $aggregationResult
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    private function extractPriceRange(array $aggregationResult): PriceRange
    {
        $pricesData = $aggregationResult['prices']['filter_pricing_group'];

        $minPrice = Money::create((string)($pricesData['min_price']['value'] ?? 0));
        $maxPrice = Money::create((string)($pricesData['max_price']['value'] ?? 0));

        $minPrice = $minPrice->round($this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId())->getMinFractionDigits());
        $maxPrice = $maxPrice->round($this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId())->getMinFractionDigits());

        return new PriceRange($minPrice, $maxPrice);
    }

    /**
     * @param array $aggregationResult
     * @return array
     */
    private function extractParameterValueIdsByParameterId(array $aggregationResult): array
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class ProductFilterConfigIdsDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param array $aggregationElasticsearchResult
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData
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
     * @param array $aggregationResult
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

    /**
     * @param array $aggregationResult
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
     */
    protected function extractPriceRange(array $aggregationResult): PriceRange
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

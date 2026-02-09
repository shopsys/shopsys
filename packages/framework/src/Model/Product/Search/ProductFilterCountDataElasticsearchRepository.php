<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterCountDataElasticsearchRepository
{
    public function __construct(
        protected readonly Client $client,
        protected readonly ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        protected readonly AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer,
    ) {
    }

    public function getProductFilterCountDataInSearch(
        ProductFilterData $productFilterData,
        FilterQuery $baseFilterQuery,
    ): ProductFilterCountData {
        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery(
            $productFilterData,
            $baseFilterQuery,
        );
        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery(
            $productFilterData,
            $absoluteNumbersFilterQuery,
        );

        $aggregationResult = $this->client->search($absoluteNumbersFilterQuery->getAbsoluteNumbersAggregationQuery());
        $countData = $this->aggregationResultToCountDataTransformer->translateAbsoluteNumbers($aggregationResult);

        if (count($productFilterData->flags) > 0) {
            $plusFlagsQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery(
                $productFilterData,
                $baseFilterQuery,
            );
            $countData->countByFlagId = $this->calculateFlagsPlusNumbers($productFilterData, $plusFlagsQuery);
        }

        if (count($productFilterData->brands) > 0) {
            $plusBrandsQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery(
                $productFilterData,
                $baseFilterQuery,
            );
            $countData->countByBrandId = $this->calculateBrandsPlusNumbers($productFilterData, $plusBrandsQuery);
        }

        return $countData;
    }

    public function getProductFilterCountDataInCategory(
        ProductFilterData $productFilterData,
        FilterQuery $baseFilterQuery,
    ): ProductFilterCountData {
        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery(
            $productFilterData,
            $baseFilterQuery,
        );
        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery(
            $productFilterData,
            $absoluteNumbersFilterQuery,
        );
        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
            $productFilterData,
            $absoluteNumbersFilterQuery,
        );

        $aggregationResult = $this->client->search(
            $absoluteNumbersFilterQuery->getAbsoluteNumbersWithParametersQuery(),
        );
        $countData = $this->aggregationResultToCountDataTransformer->translateAbsoluteNumbersWithParameters(
            $aggregationResult,
        );

        if (count($productFilterData->flags) > 0) {
            $plusFlagsQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery(
                $productFilterData,
                $baseFilterQuery,
            );
            $plusFlagsQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
                $productFilterData,
                $plusFlagsQuery,
            );
            $countData->countByFlagId = $this->calculateFlagsPlusNumbers($productFilterData, $plusFlagsQuery);
        }

        if (count($productFilterData->brands) > 0) {
            $plusBrandsQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery(
                $productFilterData,
                $baseFilterQuery,
            );
            $plusBrandsQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
                $productFilterData,
                $plusBrandsQuery,
            );
            $countData->countByBrandId = $this->calculateBrandsPlusNumbers($productFilterData, $plusBrandsQuery);
        }

        if (count($productFilterData->parameters) > 0) {
            $plusParametersQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery(
                $productFilterData,
                $baseFilterQuery,
            );
            $plusParametersQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery(
                $productFilterData,
                $plusParametersQuery,
            );

            $this->replaceParametersPlusNumbers($productFilterData, $countData, $plusParametersQuery);
        }

        return $countData;
    }

    /**
     * @return int[]
     */
    protected function calculateFlagsPlusNumbers(
        ProductFilterData $productFilterData,
        FilterQuery $plusFlagsQuery,
    ): array {
        $flagIds = [];

        foreach ($productFilterData->flags as $flag) {
            $flagIds[] = $flag->getId();
        }
        $flagsPlusNumberResult = $this->client->search($plusFlagsQuery->getFlagsPlusNumbersQuery($flagIds));

        return $this->aggregationResultToCountDataTransformer->getFlagCount($flagsPlusNumberResult);
    }

    /**
     * @return int[]
     */
    protected function calculateBrandsPlusNumbers(
        ProductFilterData $productFilterData,
        FilterQuery $plusBrandsQuery,
    ): array {
        $brandsIds = [];

        foreach ($productFilterData->brands as $brand) {
            $brandsIds[] = $brand->getId();
        }
        $brandsPlusNumberResult = $this->client->search($plusBrandsQuery->getBrandsPlusNumbersQuery($brandsIds));

        return $this->aggregationResultToCountDataTransformer->getBrandCount($brandsPlusNumberResult);
    }

    /**
     * When calculating plus numbers for a parameter, this parameter must be excluded from filter query (clone and unset)
     */
    protected function replaceParametersPlusNumbers(
        ProductFilterData $productFilterData,
        ProductFilterCountData $countData,
        FilterQuery $plusParametersQuery,
    ): void {
        foreach ($productFilterData->parameters as $key => $currentParameterFilterData) {
            $currentFilterData = clone $productFilterData;
            unset($currentFilterData->parameters[$key]);

            $currentQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
                $currentFilterData,
                $plusParametersQuery,
            );

            $plusParameterNumbers = $this->calculateParameterPlusNumbers($currentParameterFilterData, $currentQuery);
            $this->mergeParameterCountData(
                $countData,
                $plusParameterNumbers,
                $currentParameterFilterData->parameter->getId(),
            );
        }
    }

    protected function calculateParameterPlusNumbers(
        ParameterFilterData $parameterFilterData,
        FilterQuery $parameterFilterQuery,
    ): array {
        $parameterId = $parameterFilterData->parameter->getId();
        $valuesIds = [];

        foreach ($parameterFilterData->values as $parameterValue) {
            $valuesIds[] = $parameterValue->getId();
        }

        $currentQueryResult = $this->client->search(
            $parameterFilterQuery->getParametersPlusNumbersQuery($parameterId, $valuesIds),
        );

        return $this->aggregationResultToCountDataTransformer->translateParameterValuesPlusNumbers(
            $currentQueryResult,
        );
    }

    protected function mergeParameterCountData(
        ProductFilterCountData $countData,
        array $plusParameterNumbers,
        int $parameterId,
    ): void {
        $countData->countByParameterIdAndValueId[$parameterId] = $plusParameterNumbers;
    }
}

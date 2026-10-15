<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Elasticsearch\MultipleSearchFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class ProductFilterCountDataElasticsearchRepository
{
    protected const string ABSOLUTE_NUMBERS_QUERY_NAME = 'absolute_numbers';

    protected const string FLAGS_PLUS_NUMBERS_QUERY_NAME = 'flags_plus_numbers';

    protected const string BRANDS_PLUS_NUMBERS_QUERY_NAME = 'brands_plus_numbers';

    protected const string PARAMETER_PLUS_NUMBERS_QUERY_NAME_PREFIX = 'parameter_plus_numbers_';

    protected const string SEARCH_QUERY_KEY_SEPARATOR = '|';

    public function __construct(
        protected readonly MultipleSearchFacade $multipleSearchFacade,
        protected readonly ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        protected readonly AggregationResultToProductFilterCountDataTransformer $aggregationResultToCountDataTransformer,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataRequest[] $requestsIndexedByKey
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData[] indexed by the same keys as the requests
     */
    public function getProductFilterCountDataByRequests(array $requestsIndexedByKey): array
    {
        $searchQueriesIndexedBySearchQueryKey = [];
        $requestKeysAndQueryNamesIndexedBySearchQueryKey = [];

        foreach ($requestsIndexedByKey as $requestKey => $request) {
            $searchQueriesIndexedByQueryName = $this->createSearchQueriesIndexedByQueryName($request);

            foreach ($searchQueriesIndexedByQueryName as $queryName => $searchQuery) {
                $searchQueryKey = $requestKey . static::SEARCH_QUERY_KEY_SEPARATOR . $queryName;
                $searchQueriesIndexedBySearchQueryKey[$searchQueryKey] = $searchQuery;
                $requestKeysAndQueryNamesIndexedBySearchQueryKey[$searchQueryKey] = [$requestKey, $queryName];
            }
        }

        $responsesIndexedBySearchQueryKey = $this->multipleSearchFacade->searchIndexedByKey($searchQueriesIndexedBySearchQueryKey);
        $responsesIndexedByRequestKeyAndQueryName = [];

        foreach ($responsesIndexedBySearchQueryKey as $searchQueryKey => $response) {
            [$requestKey, $queryName] = $requestKeysAndQueryNamesIndexedBySearchQueryKey[$searchQueryKey];
            $responsesIndexedByRequestKeyAndQueryName[$requestKey][$queryName] = $response;
        }

        $countDataIndexedByKey = [];

        foreach ($requestsIndexedByKey as $requestKey => $request) {
            $countDataIndexedByKey[$requestKey] = $this->createCountData(
                $request,
                $responsesIndexedByRequestKeyAndQueryName[$requestKey],
            );
        }

        return $countDataIndexedByKey;
    }

    /**
     * @return array<string, array{index: string, body: array<string, mixed>}> aggregation queries indexed by query name
     */
    protected function createSearchQueriesIndexedByQueryName(ProductFilterCountDataRequest $request): array
    {
        $productFilterData = $request->getProductFilterData();
        $baseFilterQuery = $request->getBaseFilterQuery();
        $withParameters = $request->isWithParameters();

        $searchQueriesIndexedByQueryName = [
            static::ABSOLUTE_NUMBERS_QUERY_NAME => $this->createAbsoluteNumbersQuery($request),
        ];

        if (count($productFilterData->flags) > 0) {
            $plusFlagsQuery = $this->addParametersToQueryIfRequested(
                $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $baseFilterQuery),
                $request,
            );
            $searchQueriesIndexedByQueryName[static::FLAGS_PLUS_NUMBERS_QUERY_NAME] = $plusFlagsQuery->getFlagsPlusNumbersQuery(
                $this->getFlagIds($productFilterData),
            );
        }

        if (count($productFilterData->brands) > 0) {
            $plusBrandsQuery = $this->addParametersToQueryIfRequested(
                $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $baseFilterQuery),
                $request,
            );
            $searchQueriesIndexedByQueryName[static::BRANDS_PLUS_NUMBERS_QUERY_NAME] = $plusBrandsQuery->getBrandsPlusNumbersQuery(
                $this->getBrandIds($productFilterData),
            );
        }

        if ($withParameters && count($productFilterData->parameters) > 0) {
            $plusParametersQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $baseFilterQuery);
            $plusParametersQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $plusParametersQuery);

            $parametersPlusNumbersQueriesIndexedByQueryName = $this->createParametersPlusNumbersQueriesIndexedByQueryName($productFilterData, $plusParametersQuery);

            foreach ($parametersPlusNumbersQueriesIndexedByQueryName as $queryName => $searchQuery) {
                $searchQueriesIndexedByQueryName[$queryName] = $searchQuery;
            }
        }

        return $searchQueriesIndexedByQueryName;
    }

    /**
     * @return array{index: string, body: array<string, mixed>}
     */
    protected function createAbsoluteNumbersQuery(ProductFilterCountDataRequest $request): array
    {
        $productFilterData = $request->getProductFilterData();

        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery(
            $productFilterData,
            $request->getBaseFilterQuery(),
        );
        $absoluteNumbersFilterQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery(
            $productFilterData,
            $absoluteNumbersFilterQuery,
        );
        $absoluteNumbersFilterQuery = $this->addParametersToQueryIfRequested($absoluteNumbersFilterQuery, $request);

        return $request->isWithParameters()
            ? $absoluteNumbersFilterQuery->getAbsoluteNumbersWithParametersQuery()
            : $absoluteNumbersFilterQuery->getAbsoluteNumbersAggregationQuery();
    }

    protected function addParametersToQueryIfRequested(
        FilterQuery $filterQuery,
        ProductFilterCountDataRequest $request,
    ): FilterQuery {
        if (!$request->isWithParameters()) {
            return $filterQuery;
        }

        return $this->productFilterDataToQueryTransformer->addParametersToQuery($request->getProductFilterData(), $filterQuery);
    }

    /**
     * When calculating plus numbers for a parameter, this parameter must be excluded from filter query (clone and unset)
     *
     * @return array<string, array{index: string, body: array<string, mixed>}> aggregation queries indexed by query name
     */
    protected function createParametersPlusNumbersQueriesIndexedByQueryName(
        ProductFilterData $productFilterData,
        FilterQuery $plusParametersQuery,
    ): array {
        $searchQueriesIndexedByQueryName = [];

        foreach ($productFilterData->parameters as $key => $currentParameterFilterData) {
            $currentFilterData = clone $productFilterData;
            unset($currentFilterData->parameters[$key]);

            $currentQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
                $currentFilterData,
                $plusParametersQuery,
            );
            $parameterId = $currentParameterFilterData->parameter->getId();

            $searchQueriesIndexedByQueryName[$this->getParameterPlusNumbersQueryName($parameterId)] = $currentQuery->getParametersPlusNumbersQuery(
                $parameterId,
                $this->getParameterValueIds($currentParameterFilterData),
            );
        }

        return $searchQueriesIndexedByQueryName;
    }

    /**
     * @param array<string, array<string, mixed>> $responsesIndexedByQueryName
     */
    protected function createCountData(
        ProductFilterCountDataRequest $request,
        array $responsesIndexedByQueryName,
    ): ProductFilterCountData {
        $productFilterData = $request->getProductFilterData();
        $absoluteNumbersResponse = $responsesIndexedByQueryName[static::ABSOLUTE_NUMBERS_QUERY_NAME];

        $countData = $request->isWithParameters()
            ? $this->aggregationResultToCountDataTransformer->translateAbsoluteNumbersWithParameters($absoluteNumbersResponse)
            : $this->aggregationResultToCountDataTransformer->translateAbsoluteNumbers($absoluteNumbersResponse);

        if (array_key_exists(static::FLAGS_PLUS_NUMBERS_QUERY_NAME, $responsesIndexedByQueryName)) {
            $countData->countByFlagId = $this->aggregationResultToCountDataTransformer->getFlagCount(
                $responsesIndexedByQueryName[static::FLAGS_PLUS_NUMBERS_QUERY_NAME],
            );
        }

        if (array_key_exists(static::BRANDS_PLUS_NUMBERS_QUERY_NAME, $responsesIndexedByQueryName)) {
            $countData->countByBrandId = $this->aggregationResultToCountDataTransformer->getBrandCount(
                $responsesIndexedByQueryName[static::BRANDS_PLUS_NUMBERS_QUERY_NAME],
            );
        }

        foreach ($productFilterData->parameters as $parameterFilterData) {
            $parameterId = $parameterFilterData->parameter->getId();
            $queryName = $this->getParameterPlusNumbersQueryName($parameterId);

            if (!array_key_exists($queryName, $responsesIndexedByQueryName)) {
                continue;
            }

            $this->mergeParameterCountData(
                $countData,
                $this->aggregationResultToCountDataTransformer->translateParameterValuesPlusNumbers($responsesIndexedByQueryName[$queryName]),
                $parameterId,
            );
        }

        return $countData;
    }

    protected function getParameterPlusNumbersQueryName(int $parameterId): string
    {
        return static::PARAMETER_PLUS_NUMBERS_QUERY_NAME_PREFIX . $parameterId;
    }

    /**
     * @return int[]
     */
    protected function getFlagIds(ProductFilterData $productFilterData): array
    {
        $flagIds = [];

        foreach ($productFilterData->flags as $flag) {
            $flagIds[] = $flag->getId();
        }

        return $flagIds;
    }

    /**
     * @return int[]
     */
    protected function getBrandIds(ProductFilterData $productFilterData): array
    {
        $brandIds = [];

        foreach ($productFilterData->brands as $brand) {
            $brandIds[] = $brand->getId();
        }

        return $brandIds;
    }

    /**
     * @return int[]
     */
    protected function getParameterValueIds(ParameterFilterData $parameterFilterData): array
    {
        $parameterValueIds = [];

        foreach ($parameterFilterData->values as $parameterValue) {
            $parameterValueIds[] = $parameterValue->getId();
        }

        return $parameterValueIds;
    }

    /**
     * @param int[] $plusParameterNumbers
     */
    protected function mergeParameterCountData(
        ProductFilterCountData $countData,
        array $plusParameterNumbers,
        int $parameterId,
    ): void {
        $countData->countByParameterIdAndValueId[$parameterId] = $plusParameterNumbers;
    }
}

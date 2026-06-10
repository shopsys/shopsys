<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

use Monolog\Logger;
use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem;
use Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItem;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxIndexNotRecognizedException;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxRecommendationBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Product\Filter\LuigisBoxFacetsToProductFilterOptionsMapper;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;
use UnexpectedValueException;

class LuigisBoxClient
{
    protected const int COUNT_OF_DYNAMIC_PARAMETER_FILTERS = 15;

    /**
     * @param \Symfony\Component\HttpClient\TraceableHttpClient $httpClient
     */
    public function __construct(
        protected readonly string $luigisBoxApiUrl,
        protected readonly array $trackerIdsByDomainIds,
        protected readonly Domain $domain,
        protected readonly Logger $logger,
        protected readonly LuigisBoxEndpointEnum $luigisBoxEndpointEnum,
        protected readonly HttpClientInterface $httpClient,
    ) {
    }

    protected function checkNecessaryConfigurationIsSet(): void
    {
        if (array_key_exists($this->domain->getId(), $this->trackerIdsByDomainIds) === false) {
            throw new LuigisBoxIndexNotRecognizedException(
                sprintf('Luigi\'s Box tracker ID is not set for domain with ID: "%d".', $this->domain->getId()),
            );
        }
    }

    protected function getTrackerId(): string
    {
        return $this->trackerIdsByDomainIds[$this->domain->getId()];
    }

    /**
     * @param array<string, int> $limitsByType
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    public function getData(
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): array {
        $this->checkNecessaryConfigurationIsSet();

        try {
            $response = $this->sendRequest($luigisBoxBatchLoadData, $limitsByType);
        } catch (Throwable $e) {
            $this->logRequestFailure($e, $luigisBoxBatchLoadData);

            return $this->getEmptyResults(array_keys($limitsByType));
        }

        return $this->processResponse($response, $luigisBoxBatchLoadData, $limitsByType);
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData[] $luigisBoxBatchLoadDataItems
     * @return array<int|string, \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]>
     */
    public function getDataForMultiple(array $luigisBoxBatchLoadDataItems): array
    {
        $this->checkNecessaryConfigurationIsSet();

        $responses = [];
        $resultsByKey = [];

        foreach ($luigisBoxBatchLoadDataItems as $key => $luigisBoxBatchLoadDataItem) {
            $limitsByType = [$luigisBoxBatchLoadDataItem->getType() => $luigisBoxBatchLoadDataItem->getLimit()];

            try {
                $responses[$key] = $this->sendRequest($luigisBoxBatchLoadDataItem, $limitsByType);
            } catch (Throwable $e) {
                $this->logRequestFailure($e, $luigisBoxBatchLoadDataItem);
                $resultsByKey[$key] = $this->getEmptyResults(array_keys($limitsByType));
            }
        }

        foreach ($responses as $key => $response) {
            $luigisBoxBatchLoadDataItem = $luigisBoxBatchLoadDataItems[$key];

            $resultsByKey[$key] = $this->processResponse(
                $response,
                $luigisBoxBatchLoadDataItem,
                [$luigisBoxBatchLoadDataItem->getType() => $luigisBoxBatchLoadDataItem->getLimit()],
            );
        }

        return $resultsByKey;
    }

    /**
     * @param array<string, int> $limitsByType
     */
    protected function sendRequest(
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): ResponseInterface {
        $endpoint = $luigisBoxBatchLoadData->getEndpoint();
        $this->luigisBoxEndpointEnum->validateCase($endpoint);

        return $this->httpClient->request(
            $endpoint === LuigisBoxEndpointEnum::RECOMMENDATIONS ? 'POST' : 'GET',
            $this->getLuigisBoxApiUrl(
                $luigisBoxBatchLoadData,
                $limitsByType,
            ),
            $this->getRequestOptions($luigisBoxBatchLoadData),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function getRequestOptions(LuigisBoxBatchLoadData $luigisBoxBatchLoadData): array
    {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ];

        $body = $this->getBody($luigisBoxBatchLoadData);

        if ($body !== []) {
            $options['body'] = json_encode([$body], JSON_THROW_ON_ERROR);
        }

        return $options;
    }

    protected function logRequestFailure(
        Throwable $exception,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        ?ResponseInterface $response = null,
    ): void {
        $this->logger->error(
            'Luigi\'s Box API request failed.',
            [
                'exception' => $exception,
                'luigisBoxBatchLoadData' => $luigisBoxBatchLoadData,
                'options' => $this->getRequestOptions($luigisBoxBatchLoadData),
                'response' => $response !== null ? $this->getResponseDataForLogging($response) : null,
            ],
        );
    }

    /**
     * @param array<string, int> $limitsByType
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    protected function processResponse(
        ResponseInterface $response,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): array {
        $endpoint = $luigisBoxBatchLoadData->getEndpoint();

        try {
            $data = $response->toArray();

            if ($endpoint === LuigisBoxEndpointEnum::SEARCH) {
                $data = $this->getSearchResultsData($data);
            }

            if ($endpoint === LuigisBoxEndpointEnum::RECOMMENDATIONS) {
                $data = $this->getRecommendationsData($data);
            }

            $this->validateResponseData($data, $endpoint);

            return $this->getResultsIndexedByItemType($data, $endpoint, array_keys($limitsByType));
        } catch (Throwable $e) {
            $this->logRequestFailure($e, $luigisBoxBatchLoadData, $response);

            return $this->getEmptyResults(array_keys($limitsByType));
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getSearchResultsData(array $data): array
    {
        if (!array_key_exists('results', $data) || !is_array($data['results'])) {
            throw new UnexpectedValueException('Missing search results payload.');
        }

        return $data['results'];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getRecommendationsData(array $data): array
    {
        $result = array_first($data);

        if (!is_array($result)) {
            throw new UnexpectedValueException('Missing recommendations payload.');
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function validateResponseData(array $data, string $endpoint): void
    {
        if (!array_key_exists('hits', $data) || !is_array($data['hits'])) {
            throw new UnexpectedValueException('Missing hits payload.');
        }

        foreach ($data['hits'] as $hit) {
            if (!is_array($hit) || !array_key_exists('url', $hit) || !is_string($hit['url'])) {
                throw new UnexpectedValueException('Missing hit URL payload.');
            }
        }

        if (
            $endpoint === LuigisBoxEndpointEnum::AUTOCOMPLETE
            && (
                !array_key_exists('exact_match_hits_count', $data)
                || !array_key_exists('partial_match_hits_count', $data)
            )
        ) {
            throw new UnexpectedValueException('Missing autocomplete total count payload.');
        }

        if (
            $endpoint !== LuigisBoxEndpointEnum::AUTOCOMPLETE
            && $endpoint !== LuigisBoxEndpointEnum::RECOMMENDATIONS
            && !array_key_exists('total_hits', $data)
        ) {
            throw new UnexpectedValueException('Missing total hits payload.');
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getResponseDataForLogging(ResponseInterface $response): ?array
    {
        try {
            return $response->toArray(false);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @param string[] $types
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    protected function getEmptyResults(array $types): array
    {
        $resultsByType = [];

        foreach ($types as $type) {
            $resultsByType[$type] = new LuigisBoxResult([], [], 0, []);
        }

        return $resultsByType;
    }

    /**
     * @param array<string, int[]> $idsByType
     */
    protected function getTotalHitsFromDataByType(array $data, string $endpoint, string $type, array $idsByType): int
    {
        if ($endpoint === LuigisBoxEndpointEnum::AUTOCOMPLETE) {
            return count($idsByType[$type] ?? []);
        }

        if ($endpoint === LuigisBoxEndpointEnum::RECOMMENDATIONS) {
            return count($data['hits']);
        }

        return $data['total_hits'];
    }

    /**
     * @param array<string, int> $limitsByType
     */
    protected function getLuigisBoxApiUrl(
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): string {
        $url = $this->getUrlWithBasicParameters($luigisBoxBatchLoadData);

        if ($luigisBoxBatchLoadData instanceof LuigisBoxSearchBatchLoadData) {
            $url = $this->addSearchSpecificParametersToUrl($url, $luigisBoxBatchLoadData);
            $url = $this->addAutocompleteSpecificParametersToUrl($url, $luigisBoxBatchLoadData, $limitsByType);
        }

        return $url;
    }

    protected function getUrlWithBasicParameters(LuigisBoxBatchLoadData $luigisBoxBatchLoadData): string
    {
        return $this->luigisBoxApiUrl .
            $luigisBoxBatchLoadData->getEndpoint() .
            '?tracker_id=' . $this->getTrackerId() .
            '&hit_fields=url' .
            '&user_id=' . $luigisBoxBatchLoadData->getUserIdentifier();
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData $luigisBoxBatchLoadData
     */
    protected function addSearchSpecificParametersToUrl(
        string $url,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
    ): string {
        if ($luigisBoxBatchLoadData->getEndpoint() === LuigisBoxEndpointEnum::SEARCH) {
            $url .=
                '&q=' . urlencode($luigisBoxBatchLoadData->getQuery()) .
                '&remove_fields=nested' .
                '&size=' . $luigisBoxBatchLoadData->getLimit() .
                '&dynamic_facets_size=' . $this->getNumberOfDynamicalFacetsWithoutAppliedFilterFacets($luigisBoxBatchLoadData);

            if ($luigisBoxBatchLoadData->getPage() > 0) {
                $url .= '&from=' . $luigisBoxBatchLoadData->getPage();
            }

            if (count($luigisBoxBatchLoadData->getFacetNames()) > 0) {
                $url .= '&facets=' . implode(',', $luigisBoxBatchLoadData->getFacetNames());
            }

            foreach ($luigisBoxBatchLoadData->getFilter() as $key => $filterValues) {
                foreach ($filterValues as $filterValue) {
                    $url .= '&' . $key . '[]=' . urlencode($filterValue);
                }
            }

            $orderingMode = $this->getOrderingMode($luigisBoxBatchLoadData->getOrderingMode());

            if ($orderingMode !== null) {
                $url .= '&sort=' . $orderingMode;
            }
        }

        return $url;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     */
    protected function addAutocompleteSpecificParametersToUrl(
        string $url,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): string {
        if ($luigisBoxBatchLoadData->getEndpoint() === LuigisBoxEndpointEnum::AUTOCOMPLETE) {
            $url .= '&q=' . urlencode($luigisBoxBatchLoadData->getQuery());

            if (count($limitsByType) > 0) {
                $url .= '&type=' . $this->mapLimitsByTypeToLuigisBoxLimit($limitsByType);
            }
        }

        return $url;
    }

    protected function getBody(
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
    ): array {
        $body = [];

        if ($luigisBoxBatchLoadData instanceof LuigisBoxRecommendationBatchLoadData) {
            $body = $this->addRecommendationSpecificParametersToBody($body, $luigisBoxBatchLoadData);
        }

        return $body;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxRecommendationBatchLoadData $luigisBoxBatchLoadData
     */
    protected function addRecommendationSpecificParametersToBody(
        array $body,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
    ): array {
        if ($luigisBoxBatchLoadData->getEndpoint() === LuigisBoxEndpointEnum::RECOMMENDATIONS) {
            $body['recommendation_type'] = $luigisBoxBatchLoadData->getType();
            $body['user_id'] = $luigisBoxBatchLoadData->getUserIdentifier();
            $body['size'] = $luigisBoxBatchLoadData->getLimit();
            $body['hit_fields'] = ['url'];
            $body['recommender_client_identifier'] = $luigisBoxBatchLoadData->getRecommenderClientIdentifier();

            if (count($luigisBoxBatchLoadData->getItemIds()) > 0) {
                $body['item_ids'] = $luigisBoxBatchLoadData->getItemIds();
            }
        }

        return $body;
    }

    protected function getOrderingMode(?string $orderingMode): ?string
    {
        return match ($orderingMode) {
            ProductListOrderingConfig::ORDER_BY_NAME_ASC => 'name:asc',
            ProductListOrderingConfig::ORDER_BY_NAME_DESC => 'name:desc',
            ProductListOrderingConfig::ORDER_BY_PRICE_ASC => 'price_amount:asc',
            ProductListOrderingConfig::ORDER_BY_PRICE_DESC => 'price_amount:desc',
            default => null,
        };
    }

    protected function getIdFromIdentity(string $identity): int
    {
        return (int)str_replace(
            [
                LuigisBoxProductFeedItem::UNIQUE_IDENTIFIER_PREFIX,
                LuigisBoxCategoryFeedItem::UNIQUE_IDENTIFIER_PREFIX,
                LuigisBoxArticleFeedItem::UNIQUE_BLOG_ARTICLE_IDENTIFIER_PREFIX,
                LuigisBoxArticleFeedItem::UNIQUE_ARTICLE_IDENTIFIER_PREFIX,
                LuigisBoxBrandFeedItem::UNIQUE_BRAND_IDENTIFIER_PREFIX,
                '-',
            ],
            '',
            $identity,
        );
    }

    /**
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    protected function getResultsIndexedByItemType(array $data, string $endpoint, array $types): array
    {
        $idsByType = [];
        $idsWithPrefixByType = [];
        $resultsByType = [];
        $hits = $data['hits'];

        foreach ($hits as $hit) {
            $idsWithPrefixByType[$this->getTypeFromHitUrl($hit['url'])][] = $hit['url'];
            $idsByType[$this->getTypeFromHitUrl($hit['url'])][] = $this->getIdFromIdentity($hit['url']);
        }

        foreach ($types as $type) {
            $resultsByType[$type] = new LuigisBoxResult(
                $idsByType[$type] ?? [],
                $idsWithPrefixByType[$type] ?? [],
                $this->getTotalHitsFromDataByType($data, $endpoint, $type, $idsByType),
                $data['facets'] ?? [],
            );
        }

        return $resultsByType;
    }

    protected function getTypeFromHitUrl(string $hitUrl): string
    {
        $type = explode('-', $hitUrl)[0];

        if ($type === LuigisBoxArticleFeedItem::UNIQUE_BLOG_ARTICLE_IDENTIFIER_PREFIX) {
            return LuigisBoxArticleFeedItem::UNIQUE_ARTICLE_IDENTIFIER_PREFIX;
        }

        if ($type === 'product') {
            return TypeInLuigisBoxEnum::PRODUCT;
        }

        return $type;
    }

    /**
     * @param array<string, int> $limitsByType
     */
    protected function mapLimitsByTypeToLuigisBoxLimit(array $limitsByType): string
    {
        $luigisBoxLimits = [];

        foreach ($limitsByType as $type => $limitByType) {
            $luigisBoxLimits[] = $type . ':' . $limitByType;
        }

        return implode(',', $luigisBoxLimits);
    }

    protected function getNumberOfDynamicalFacetsWithoutAppliedFilterFacets(
        LuigisBoxSearchBatchLoadData $luigisBoxBatchLoadData,
    ): int {
        if ($luigisBoxBatchLoadData->getType() === TypeInLuigisBoxEnum::PRODUCT) {
            return max(
                0,
                static::COUNT_OF_DYNAMIC_PARAMETER_FILTERS - (
                    count($luigisBoxBatchLoadData->getFacetNames()) -
                    count(LuigisBoxFacetsToProductFilterOptionsMapper::PRODUCT_FACET_NAMES)
                ),
            );
        }

        return static::COUNT_OF_DYNAMIC_PARAMETER_FILTERS;
    }
}

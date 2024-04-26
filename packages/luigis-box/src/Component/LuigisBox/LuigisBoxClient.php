<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

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
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class LuigisBoxClient
{
    protected const int COUNT_OF_DYNAMIC_PARAMETER_FILTERS = 5;

    /**
     * @param string $luigisBoxApiUrl
     * @param array $trackerIdsByDomainIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum $luigisBoxEndpointEnum
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

    /**
     * @return string
     */
    protected function getTrackerId(): string
    {
        return $this->trackerIdsByDomainIds[$this->domain->getId()];
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    public function getData(
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): array {
        $endpoint = $luigisBoxBatchLoadData->getEndpoint();
        $this->checkNecessaryConfigurationIsSet();
        $this->luigisBoxEndpointEnum->validateCase($endpoint);

        $options = [
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ];

        $body = $this->getBody($luigisBoxBatchLoadData);

        if ($body !== []) {
            $options['body'] = json_encode([$body], JSON_THROW_ON_ERROR);
        }

        try {
            $response = $this->httpClient->request(
                $endpoint === LuigisBoxEndpointEnum::RECOMMENDATIONS ? 'POST' : 'GET',
                $this->getLuigisBoxApiUrl(
                    $luigisBoxBatchLoadData,
                    $limitsByType,
                ),
                $options,
            );

            $data = $response->toArray();
        } catch (Throwable $e) {
            $this->logger->error(
                'Luigi\'s Box API request failed.',
                [
                    'exception' => $e,
                    'luigisBoxBatchLoadData' => $luigisBoxBatchLoadData,
                ],
            );

            return $this->getEmptyResults(array_keys($limitsByType));
        }

        if ($endpoint === LuigisBoxEndpointEnum::SEARCH) {
            $data = $data['results'];
        }

        if ($endpoint === LuigisBoxEndpointEnum::RECOMMENDATIONS) {
            $data = reset($data);
        }

        return $this->getResultsIndexedByItemType($data, $endpoint, array_keys($limitsByType));
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
     * @param array $data
     * @param string $endpoint
     * @return int
     */
    protected function getTotalHitsFromData(array $data, string $endpoint): int
    {
        if ($endpoint === LuigisBoxEndpointEnum::AUTOCOMPLETE) {
            return (int)$data['exact_match_hits_count'] + (int)$data['partial_match_hits_count'];
        }

        if ($endpoint === LuigisBoxEndpointEnum::RECOMMENDATIONS) {
            return count($data['hits']);
        }

        return $data['total_hits'];
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     * @return string
     */
    protected function getLuigisBoxApiUrl(
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): string {
        $url = $this->getUrlWithBasicParameters($luigisBoxBatchLoadData);

        if ($luigisBoxBatchLoadData instanceof LuigisBoxSearchBatchLoadData) {
            $url = $this->addSearchSpecificParametersToUrl($url, $luigisBoxBatchLoadData, $limitsByType);
            $url = $this->addAutocompleteSpecificParametersToUrl($url, $luigisBoxBatchLoadData, $limitsByType);
        }

        return $url;
    }

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData $luigisBoxBatchLoadData
     * @return string
     */
    protected function getUrlWithBasicParameters(LuigisBoxBatchLoadData $luigisBoxBatchLoadData): string
    {
        return $this->luigisBoxApiUrl .
            $luigisBoxBatchLoadData->getEndpoint() .
            '?tracker_id=' . $this->getTrackerId() .
            '&hit_fields=url' .
            '&user_id=' . $luigisBoxBatchLoadData->getUserIdentifier();
    }

    /**
     * @param string $url
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     * @return string
     */
    protected function addSearchSpecificParametersToUrl(
        string $url,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): string {
        if ($luigisBoxBatchLoadData->getEndpoint() === LuigisBoxEndpointEnum::SEARCH) {
            $quicksearchTypesWithLimits = $this->getQuicksearchTypesWithLimits($limitsByType);

            $url .=
                '&q=' . urlencode($luigisBoxBatchLoadData->getQuery()) .
                '&remove_fields=nested' .
                '&size=' . $this->getMainTypeLimit($limitsByType) .
                '&dynamic_facets_size=' . static::COUNT_OF_DYNAMIC_PARAMETER_FILTERS;

            if ($luigisBoxBatchLoadData->getPage() > 0) {
                $url .= '&from=' . $luigisBoxBatchLoadData->getPage();
            }

            $url .= $quicksearchTypesWithLimits !== '' ? '&quicksearch_types=' . $quicksearchTypesWithLimits : '';

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
     * @param string $url
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     * @return string
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

    /**
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData $luigisBoxBatchLoadData
     * @return array
     */
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
     * @param array $body
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxRecommendationBatchLoadData $luigisBoxBatchLoadData
     * @return array
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

            if (count($luigisBoxBatchLoadData->getItemIds()) > 0) {
                $body['item_ids'] = $luigisBoxBatchLoadData->getItemIds();
            }
        }

        return $body;
    }

    /**
     * @param string|null $orderingMode
     * @return string|null
     */
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

    /**
     * @param string $identity
     * @return int
     */
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
     * @param array $types
     * @return string
     */
    protected function getMainType(array $types): string
    {
        if (in_array(TypeInLuigisBoxEnum::PRODUCT, $types, true)) {
            return TypeInLuigisBoxEnum::PRODUCT;
        }

        return reset($types);
    }

    /**
     * @param array<string, int> $typesWithLimits
     * @return string
     */
    protected function getQuicksearchTypesWithLimits(array $typesWithLimits): string
    {
        $quicksearchTypesWithLimit = [];

        foreach ($typesWithLimits as $type => $limit) {
            if ($type !== $this->getMainType(array_keys($typesWithLimits))) {
                $quicksearchTypesWithLimit[] = $type . ':' . $limit;
            }
        }

        return implode(',', $quicksearchTypesWithLimit);
    }

    /**
     * @param array $limitsByType
     * @return int
     */
    protected function getMainTypeLimit(array $limitsByType): int
    {
        return $limitsByType[$this->getMainType(array_keys($limitsByType))];
    }

    /**
     * @param array $data
     * @param string $endpoint
     * @param array $types
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    protected function getResultsIndexedByItemType(array $data, string $endpoint, array $types): array
    {
        $idsByType = [];
        $idsWithPrefixByType = [];
        $resultsByType = [];
        $hits = array_merge($data['hits'], $data['quicksearch_hits'] ?? []);

        foreach ($hits as $hit) {
            $idsWithPrefixByType[$this->getTypeFromHitUrl($hit['url'])][] = $hit['url'];
            $idsByType[$this->getTypeFromHitUrl($hit['url'])][] = $this->getIdFromIdentity($hit['url']);
        }

        foreach ($types as $type) {
            $resultsByType[$type] = new LuigisBoxResult(
                $idsByType[$type] ?? [],
                $idsWithPrefixByType[$type] ?? [],
                $this->getTotalHitsFromData($data, $endpoint),
                $data['facets'] ?? [],
            );
        }

        return $resultsByType;
    }

    /**
     * @param string $hitUrl
     * @return string
     */
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
     * @return string
     */
    protected function mapLimitsByTypeToLuigisBoxLimit(array $limitsByType): string
    {
        $luigisBoxLimits = [];

        foreach ($limitsByType as $type => $limitByType) {
            $luigisBoxLimits[] = $type . ':' . $limitByType;
        }

        return implode(',', $luigisBoxLimits);
    }
}

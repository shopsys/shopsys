<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem;
use Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItem;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxActionNotRecognizedException;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxIndexNotRecognizedException;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;
use Symfony\Bridge\Monolog\Logger;
use Throwable;

class LuigisBoxClient
{
    public const string ACTION_SEARCH = 'search';
    public const string ACTION_AUTOCOMPLETE = 'autocomplete/v2';

    /**
     * @param string $luigisBoxApiUrl
     * @param array $trackerIdsByDomainIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(
        protected readonly string $luigisBoxApiUrl,
        protected readonly array $trackerIdsByDomainIds,
        protected readonly Domain $domain,
        protected readonly Logger $logger,
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
        $action = $luigisBoxBatchLoadData->getEndpoint();
        $this->checkNecessaryConfigurationIsSet();
        $this->validateActionIsValid($action);

        try {
            $data = json_decode(
                file_get_contents(
                    $this->getLuigisBoxApiUrl(
                        $luigisBoxBatchLoadData,
                        $limitsByType,
                    ),
                ),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
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

        if ($action === static::ACTION_SEARCH) {
            $data = $data['results'];
        }

        return $this->getResultsIndexedByItemType($data, $action, array_keys($limitsByType));
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
     * @param string $action
     * @return int
     */
    protected function getTotalHitsFromData(array $data, string $action): int
    {
        if ($action === static::ACTION_AUTOCOMPLETE) {
            return (int)$data['exact_match_hits_count'] + (int)$data['partial_match_hits_count'];
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
        $url = $this->luigisBoxApiUrl .
            $luigisBoxBatchLoadData->getEndpoint() . '/' .
            '?tracker_id=' . $this->getTrackerId() .
            '&q=' . urlencode($luigisBoxBatchLoadData->getQuery()) .
            '&hit_fields=url' .
            '&user_id=' . $luigisBoxBatchLoadData->getUserIdentifier();

        $url = $this->addSearchSpecificParameters($url, $luigisBoxBatchLoadData, $limitsByType);

        return $this->addAutocompleteSpecificParameters($url, $luigisBoxBatchLoadData, $limitsByType);
    }

    /**
     * @param string $url
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     * @return string
     */
    protected function addSearchSpecificParameters(
        string $url,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): string {
        if ($luigisBoxBatchLoadData->getEndpoint() === static::ACTION_SEARCH) {
            $quicksearchTypesWithLimits = $this->getQuicksearchTypesWithLimits($limitsByType);

            $url .=
                '&remove_fields=nested' .
                '&size=' . $this->getMainTypeLimit($limitsByType) .
                '&from=' . $luigisBoxBatchLoadData->getPage();
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
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData $luigisBoxBatchLoadData
     * @param array<string, int> $limitsByType
     * @return string
     */
    protected function addAutocompleteSpecificParameters(
        string $url,
        LuigisBoxBatchLoadData $luigisBoxBatchLoadData,
        array $limitsByType,
    ): string {
        if ($luigisBoxBatchLoadData->getEndpoint() === static::ACTION_AUTOCOMPLETE && count($limitsByType) > 0) {
            $url .= '&type=' . $this->mapLimitsByTypeToLuigisBoxLimit($limitsByType);
        }

        return $url;
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
     * @param string $action
     * @param array $types
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    protected function getResultsIndexedByItemType(array $data, string $action, array $types): array
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
                $this->getTotalHitsFromData($data, $action),
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

    /**
     * @param string $action
     */
    protected function validateActionIsValid(string $action): void
    {
        if (!in_array($action, [static::ACTION_SEARCH, static::ACTION_AUTOCOMPLETE], true)) {
            throw new LuigisBoxActionNotRecognizedException($action);
        }
    }
}

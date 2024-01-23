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
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;

class LuigisBoxClient
{
    public const string ACTION_SEARCH = 'search';
    public const string ACTION_AUTOCOMPLETE = 'autocomplete/v2';

    public const string TYPE_IN_LUIGIS_BOX_ARTICLE = 'article';
    public const string TYPE_IN_LUIGIS_BOX_BRAND = 'brand';
    public const string TYPE_IN_LUIGIS_BOX_CATEGORY = 'category';
    public const string TYPE_IN_LUIGIS_BOX_PRODUCT = 'item';

    /**
     * @param string $luigisBoxApiUrl
     * @param array $trackerIdsByDomainIds
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly string $luigisBoxApiUrl,
        protected readonly array $trackerIdsByDomainIds,
        protected readonly Domain $domain,
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
     * @param string $query
     * @param string $action
     * @param int $page
     * @param array<string, int> $limitsByType
     * @param array $filter
     * @param string $userIdentifier
     * @param string|null $orderingMode
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    public function getData(
        string $query,
        string $action,
        int $page,
        array $limitsByType,
        array $filter,
        string $userIdentifier,
        ?string $orderingMode,
    ): array {
        $this->checkNecessaryConfigurationIsSet();
        $this->validateActionIsValid($action);

        $data = json_decode(
            file_get_contents(
                $this->getLuigisBoxApiUrl(
                    $query,
                    $action,
                    $page,
                    $limitsByType,
                    $filter,
                    $userIdentifier,
                    $orderingMode,
                ),
            ),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if ($action === self::ACTION_SEARCH) {
            $data = $data['results'];
        }

        return $this->getResultsIndexedByItemType($data, $action, array_keys($limitsByType));
    }

    /**
     * @param array $data
     * @param string $action
     * @return int
     */
    protected function getTotalHitsFromData(array $data, string $action): int
    {
        if ($action === self::ACTION_AUTOCOMPLETE) {
            return (int)$data['exact_match_hits_count'] + (int)$data['partial_match_hits_count'];
        }

        return $data['total_hits'];
    }

    /**
     * @param string $query
     * @param string $action
     * @param int $page
     * @param array $limitsByType
     * @param array $filter
     * @param string $userIdentifier
     * @param string|null $orderingMode
     * @return string
     */
    protected function getLuigisBoxApiUrl(
        string $query,
        string $action,
        int $page,
        array $limitsByType,
        array $filter,
        string $userIdentifier,
        ?string $orderingMode,
    ): string {
        $url = $this->luigisBoxApiUrl .
            $action . '/' .
            '?tracker_id=' . $this->getTrackerId() .
            '&q=' . urlencode($query) .
            '&hit_fields=url' .
            '&user_id=' . $userIdentifier;

        if ($action === self::ACTION_SEARCH) {
            $quicksearchTypesWithLimits = $this->getQuicksearchTypesWithLimits($limitsByType);

            $url .=
                '&remove_fields=nested' .
                '&size=' . $this->getMainTypeLimit($limitsByType) .
                '&from=' . $page;
            $url .=
                $quicksearchTypesWithLimits !== '' ? '&quicksearch_types=' . $quicksearchTypesWithLimits : '';

            foreach ($filter as $key => $filterValues) {
                foreach ($filterValues as $filterValue) {
                    $url .= '&' . $key . '[]=' . urlencode($filterValue);
                }
            }

            $orderingMode = $this->getOrderingMode($orderingMode);

            if ($orderingMode !== null) {
                $url .= '&sort=' . $orderingMode;
            }
        }

        if ($action === self::ACTION_AUTOCOMPLETE && count($limitsByType) > 0) {
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
            ProductListOrderingConfig::ORDER_BY_PRICE_ASC => 'price:asc',
            ProductListOrderingConfig::ORDER_BY_PRICE_DESC => 'price:desc',
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
        if (in_array(static::TYPE_IN_LUIGIS_BOX_PRODUCT, $types, true)) {
            return self::TYPE_IN_LUIGIS_BOX_PRODUCT;
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
            return static::TYPE_IN_LUIGIS_BOX_PRODUCT;
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
        if (!in_array($action, [self::ACTION_SEARCH, self::ACTION_AUTOCOMPLETE], true)) {
            throw new LuigisBoxActionNotRecognizedException($action);
        }
    }
}

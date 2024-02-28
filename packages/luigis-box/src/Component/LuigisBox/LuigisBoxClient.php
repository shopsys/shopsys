<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxActionNotRecognizedException;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxIndexNotRecognizedException;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;

class LuigisBoxClient
{
    public const string MAIN_TYPE = 'product';
    public const string ACTION_SEARCH = 'search';
    public const string ACTION_AUTOCOMPLETE = 'autocomplete/v2';

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
     * @param string|null $orderingMode
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult[]
     */
    public function getData(
        string $query,
        string $action,
        int $page,
        array $limitsByType,
        array $filter,
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
     * @param string|null $orderingMode
     * @return string
     */
    protected function getLuigisBoxApiUrl(
        string $query,
        string $action,
        int $page,
        array $limitsByType,
        array $filter,
        ?string $orderingMode,
    ): string {
        $url = $this->luigisBoxApiUrl .
            $action . '/' .
            '?tracker_id=' . $this->getTrackerId() .
            '&q=' . urlencode('"' . $query . '"');

        if ($action === self::ACTION_SEARCH) {
            $url .=
                '&hit_fields=identity' .
                '&remove_fields=nested' .
                '&size=' . $this->getMainTypeLimit($limitsByType) .
                '&from=' . $page .
                count($limitsByType) > 1 ? '&quicksearch_types=' . $this->getQuicksearchTypes(array_keys($limitsByType)) : '';

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

        if ($action === self::ACTION_AUTOCOMPLETE) {
            foreach ($limitsByType as $type => $limitByType) {
                $url .= '&limit=' . $this->mapAppTypeToLuigisBoxType($type) . ':' . $limitByType;
            }
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
            ],
            '',
            $identity,
        );
    }

    /**
     * @param array $types
     * @return string
     */
    protected function getQuicksearchTypes(array $types): string
    {
        foreach ($types as $key => $type) {
            if ($type === self::MAIN_TYPE) {
                unset($types[$key]);
            }
        }

        return implode(',', $types);
    }

    /**
     * @param array $limitsByType
     * @return int
     */
    protected function getMainTypeLimit(array $limitsByType): int
    {
        return $limitsByType[self::MAIN_TYPE];
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
        $resultsByType = [];

        foreach ($data['hits'] as $hit) {
            $idsByType[$this->getTypeFromHitUrl($hit['url'])][] = $this->getIdFromIdentity($hit['url']);
        }

        foreach ($types as $type) {
            $resultsByType[$type] = new LuigisBoxResult(
                $idsByType[$type] ?? [],
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
        return explode('-', $hitUrl)[0];
    }

    /**
     * @param string $appType
     * @return string
     */
    protected function mapAppTypeToLuigisBoxType(string $appType): string
    {
        return match ($appType) {
            'product' => 'item',
            default => $appType,
        };
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

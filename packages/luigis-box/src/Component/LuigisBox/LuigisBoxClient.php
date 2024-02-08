<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Component\LuigisBox;

use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Exception\LuigisBoxIndexNotRecognizedException;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;

class LuigisBoxClient
{
    public const string LUIGIS_BOX_TYPE_PRODUCT = 'item';
    public const string LUIGIS_BOX_ENDPOINT_SEARCH = 'search';
    public const string LUIGIS_BOX_ENDPOINT_AUTOCOMPLETE = 'autocomplete/v2';

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
     * @param string $type
     * @param string $endpoint
     * @param int $page
     * @param int $limit
     * @param array $filter
     * @param string|null $orderingMode
     * @return \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult
     */
    public function getData(
        string $query,
        string $type,
        string $endpoint,
        int $page,
        int $limit,
        array $filter,
        ?string $orderingMode,
    ): LuigisBoxResult {
        $this->checkNecessaryConfigurationIsSet();

        $data = json_decode(
            file_get_contents(
                $this->getLuigisBoxApiUrl(
                    $query,
                    $type,
                    $endpoint,
                    $page,
                    $limit,
                    $filter,
                    $orderingMode,
                ),
            ),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if ($endpoint === self::LUIGIS_BOX_ENDPOINT_SEARCH) {
            $data = $data['results'];
        }

        $ids = [];

        foreach ($data['hits'] as $hit) {
            $ids[] = $this->getIdFromIdentity($hit['url']);
        }

        return new LuigisBoxResult(
            $ids,
            $this->getTotalHitsFromData($data, $endpoint),
        );
    }

    /**
     * @param array $data
     * @param string $endpoint
     * @return int
     */
    protected function getTotalHitsFromData(array $data, string $endpoint): int
    {
        if ($endpoint === self::LUIGIS_BOX_ENDPOINT_AUTOCOMPLETE) {
            return (int)$data['exact_match_hits_count'] + (int)$data['partial_match_hits_count'];
        }

        return $data['total_hits'];
    }

    /**
     * @param string $query
     * @param string $type
     * @param string $endpoint
     * @param int $offset
     * @param int $limit
     * @param array $filter
     * @param string|null $orderingMode
     * @return string
     */
    protected function getLuigisBoxApiUrl(
        string $query,
        string $type,
        string $endpoint,
        int $offset,
        int $limit,
        array $filter,
        ?string $orderingMode,
    ): string {
        $url = $this->luigisBoxApiUrl .
            $endpoint . '/' .
            '?tracker_id=' . $this->getTrackerId() .
            '&q=' . urlencode('"' . $query . '"');

        if ($endpoint === self::LUIGIS_BOX_ENDPOINT_SEARCH) {
            $url .= '&hit_fields=identity' .
                '&size=' . $limit .
                '&from=' . $offset;

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

        if ($endpoint === self::LUIGIS_BOX_ENDPOINT_AUTOCOMPLETE) {
            $url .= '&type=' . $type . ':' . $limit;
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
}

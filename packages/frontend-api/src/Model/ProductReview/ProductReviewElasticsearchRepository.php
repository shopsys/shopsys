<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview;

use Elasticsearch\Client;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportFieldProvider;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;

class ProductReviewElasticsearchRepository
{
    public function __construct(
        protected readonly Client $client,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    /**
     * Reads a single page of the reviews stored on the given document,
     * sorted and sliced by Elasticsearch via the nested inner hits
     *
     * @param int $productId the ID of the product that carries the reviews, i.e. the main variant for a variant family
     */
    public function getReviewsPage(
        int $productId,
        string $orderingMode,
        int $offset,
        int $limit,
    ): ProductReviewsPageResult {
        $result = $this->search(
            $this->filterQueryFactory->createVisibleProductsByProductIdsFilter([$productId]),
            $orderingMode,
            $offset,
            $limit,
        );

        $productArray = $this->extractProductArray($result, sprintf('Product with ID %d does not exist.', $productId));

        return new ProductReviewsPageResult(
            $this->extractReviewArrays($result),
            $this->extractTotalCount($result),
            $productArray[ProductExportFieldProvider::REVIEW_SUMMARY],
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function search(FilterQuery $filterQuery, string $orderingMode, int $offset, int $limit): array
    {
        $query = $filterQuery->getQuery();
        $query['body']['_source'] = [ProductExportFieldProvider::REVIEW_SUMMARY];
        // the optional "should" clause populates the inner hits without excluding products that have no review
        $query['body']['query']['bool']['should'][] = [
            'nested' => [
                'path' => ProductExportFieldProvider::REVIEWS,
                'query' => ['match_all' => (object)[]],
                'inner_hits' => [
                    'sort' => $this->getInnerHitsSort($orderingMode),
                    'from' => $offset,
                    'size' => $limit,
                ],
            ],
        ];

        return $this->client->search($query);
    }

    /**
     * @return array<int, array<string, string>>
     */
    protected function getInnerHitsSort(string $orderingMode): array
    {
        $newestFirst = ['reviews.created_at' => 'desc'];

        return match ($orderingMode) {
            ProductReviewOrderingModeEnum::HIGHEST_RATING => [['reviews.rating' => 'desc'], $newestFirst],
            ProductReviewOrderingModeEnum::LOWEST_RATING => [['reviews.rating' => 'asc'], $newestFirst],
            default => [$newestFirst],
        };
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    protected function extractProductArray(array $result, string $notFoundMessage): array
    {
        $hits = $result['hits']['hits'];

        if (count($hits) === 0) {
            throw new ProductNotFoundException($notFoundMessage);
        }

        return $hits[0]['_source'];
    }

    /**
     * @param array<string, mixed> $result
     * @return array<int, array<string, mixed>>
     */
    protected function extractReviewArrays(array $result): array
    {
        $innerHits = $result['hits']['hits'][0]['inner_hits'][ProductExportFieldProvider::REVIEWS]['hits']['hits'] ?? [];

        return array_map(static fn (array $innerHit) => $innerHit['_source'], $innerHits);
    }

    /**
     * @param array<string, mixed> $result
     */
    protected function extractTotalCount(array $result): int
    {
        return $result['hits']['hits'][0]['inner_hits'][ProductExportFieldProvider::REVIEWS]['hits']['total']['value'] ?? 0;
    }
}

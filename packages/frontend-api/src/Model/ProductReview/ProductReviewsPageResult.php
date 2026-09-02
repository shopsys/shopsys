<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\ProductReview;

class ProductReviewsPageResult
{
    /**
     * @param array<int, array<string, mixed>> $reviewArrays
     * @param array{average_rating: float|null, total_count: int, rating_counts: array<int, array{rating: int, count: int}>} $summary
     */
    public function __construct(
        public readonly array $reviewArrays,
        public readonly int $totalCount,
        public readonly array $summary,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Elasticsearch;

use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;

/**
 * Maps reviews to the shape they have in the product Elasticsearch document.
 * The same shape is served by the frontend API, so it is used also for reviews read from the database.
 */
class ProductReviewDocumentMapper
{
    protected const int MAX_RATING = 5;

    /**
     * Only moderated customer content and the precomputed public reviewer name are mapped,
     * so no personal data can ever reach Elasticsearch
     *
     * @return array<string, mixed>
     */
    public function mapReview(ProductReview $productReview): array
    {
        return [
            'uuid' => $productReview->getUuid(),
            'product_uuid' => $productReview->getProduct()?->getUuid(),
            'product_name' => $productReview->getProductName(),
            'reviewer_name' => $this->getPublicReviewerName($productReview),
            'rating' => $productReview->getRating(),
            'text' => $productReview->getText(),
            'created_at' => $productReview->getCreatedAt()->format(DATE_ATOM),
            'is_verified_purchase' => $productReview->isVerifiedPurchase(),
            'response_text' => $productReview->getResponseText(),
            'response_created_at' => $productReview->getResponseCreatedAt()?->format(DATE_ATOM),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[] $productReviews
     * @return array<string, mixed>
     */
    public function mapSummary(array $productReviews): array
    {
        $ratingCounts = array_fill_keys(range(static::MAX_RATING, 1), 0);
        $ratingSum = 0;

        foreach ($productReviews as $productReview) {
            $ratingCounts[$productReview->getRating()]++;
            $ratingSum += $productReview->getRating();
        }

        $totalCount = count($productReviews);

        return [
            'average_rating' => $totalCount > 0 ? round($ratingSum / $totalCount, 2) : null,
            'total_count' => $totalCount,
            'rating_counts' => array_map(
                static fn (int $rating, int $count) => [
                    'rating' => $rating,
                    'count' => $count,
                ],
                array_keys($ratingCounts),
                $ratingCounts,
            ),
        ];
    }

    protected function getPublicReviewerName(ProductReview $productReview): ?string
    {
        if ($productReview->isAnonymous()) {
            return null;
        }

        return sprintf('%s %s.', $productReview->getFirstName(), mb_substr($productReview->getLastName(), 0, 1));
    }
}

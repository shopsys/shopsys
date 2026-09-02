<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\ProductReview;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Override;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use Symfony\Component\Clock\DatePoint;

class ProductReviewResolverMap extends ResolverMap
{
    public function __construct(
        protected readonly DataLoaderInterface $productsVisibleByIdsBatchLoader,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function map(): array
    {
        return [
            'ProductReview' => [
                'uuid' => static fn (array $reviewArray) => $reviewArray['uuid'],
                'reviewerName' => static fn (array $reviewArray) => $reviewArray['reviewer_name'],
                'rating' => static fn (array $reviewArray) => $reviewArray['rating'],
                'text' => static fn (array $reviewArray) => $reviewArray['text'],
                'createdAt' => static fn (array $reviewArray) => new DatePoint($reviewArray['created_at']),
                'isVerifiedPurchase' => static fn (array $reviewArray) => $reviewArray['is_verified_purchase'],
                'responseText' => static fn (array $reviewArray) => $reviewArray['response_text'],
                'responseCreatedAt' => static fn (array $reviewArray) => $reviewArray['response_created_at'] !== null ? new DatePoint($reviewArray['response_created_at']) : null,
                // the exported documents carry approved reviews only, so the status is stored just for the own reviews read from the database
                'status' => static fn (array $reviewArray) => $reviewArray['status'] ?? ProductReviewStatusEnum::STATUS_APPROVED,
                'rejectionReason' => static fn (array $reviewArray) => $reviewArray['rejection_reason'] ?? null,
                'productUuid' => static fn (array $reviewArray) => $reviewArray['product_uuid'],
                'productName' => static fn (array $reviewArray) => $reviewArray['product_name'],
                'product' => fn (array $reviewArray) => $this->loadVisibleProduct($reviewArray['product_id'] ?? null),
            ],
            'ProductReviewsSummary' => [
                'averageRating' => static fn (array $summaryArray) => $summaryArray['average_rating'],
                'totalCount' => static fn (array $summaryArray) => $summaryArray['total_count'],
                'ratingCounts' => static fn (array $summaryArray) => $summaryArray['rating_counts'],
            ],
        ];
    }

    protected function loadVisibleProduct(?int $productId): ?Promise
    {
        if ($productId === null) {
            return null;
        }

        return $this->productsVisibleByIdsBatchLoader->load([$productId])
            ->then(static fn (array $products) => $products[0] ?? null);
    }
}

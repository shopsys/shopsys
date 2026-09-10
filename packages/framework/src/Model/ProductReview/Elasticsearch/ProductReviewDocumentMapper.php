<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview\Elasticsearch;

use Shopsys\FrameworkBundle\Component\CustomerUploadedFile\CustomerUploadedFileFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ProductReview\Image\ProductReviewImagePublisher;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;

/**
 * Maps reviews to the shape they have in the product Elasticsearch document.
 * The same shape is served by the frontend API, so it is used also for reviews read from the database.
 */
class ProductReviewDocumentMapper
{
    protected const int MAX_RATING = 5;

    public function __construct(
        protected readonly CustomerUploadedFileFacade $customerUploadedFileFacade,
        protected readonly Domain $domain,
        protected readonly ProductReviewImagePublisher $productReviewImagePublisher,
    ) {
    }

    /**
     * Only moderated customer content and the precomputed public reviewer name are mapped,
     * so no personal data can ever reach Elasticsearch
     *
     * @return array<string, mixed>
     */
    public function mapReview(ProductReview $productReview, int $domainId): array
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
            'images' => $this->mapImages($productReview, $domainId),
        ];
    }

    /**
     * Photos of an approved review are published as static files served by the standard
     * image resizing pipeline; photos of a not yet approved review are visible only
     * to their author, so they keep the hash-protected customer file URL.
     *
     * @return array<int, array{url: string, name: string}>
     */
    protected function mapImages(ProductReview $productReview, int $domainId): array
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);
        $isReviewPublic = $productReview->getStatus() === ProductReviewStatusEnum::STATUS_APPROVED;
        $images = [];

        foreach ($productReview->getImages() as $productReviewImage) {
            if ($productReviewImage->isRejected()) {
                continue;
            }

            foreach ($this->customerUploadedFileFacade->getUploadedFilesByEntity($productReviewImage) as $customerUploadedFile) {
                $images[] = [
                    'url' => $isReviewPublic
                        ? $this->productReviewImagePublisher->getPublicUrl($domainConfig, $productReviewImage, $customerUploadedFile)
                        : $this->customerUploadedFileFacade->getCustomerUploadedFileViewUrl($domainConfig, $customerUploadedFile),
                    'name' => $customerUploadedFile->getNameWithExtension(),
                ];
            }
        }

        return $images;
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

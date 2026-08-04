<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\ProductReview;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\ProductReviewDataFixture;
use App\Model\Product\Product;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductReviewsQueryTest extends GraphQlTestCase
{
    public function testReviewsOfProductContainAllPublicFields(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $productReview = $this->getReferenceForDomain(ProductReviewDataFixture::PRODUCT_REVIEW_APPROVED_TV_SHARP_PICTURE, 1, ProductReview::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'productReviews');
        $this->assertSame(9, $data['totalCount']);
        $this->assertSame('NEWEST', $data['orderingMode']);
        $this->assertSame([
            'uuid' => $productReview->getUuid(),
            'reviewerName' => 'Lena M.',
            'rating' => 4,
            'text' => t(
                'The picture is sharp and the pink design looks great in the children\'s room.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            'createdAt' => '2026-05-31T16:00:00+00:00',
            'isVerifiedPurchase' => true,
            'responseText' => t(
                'We are glad the television found the perfect place in your home.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            'responseCreatedAt' => '2026-06-02T09:00:00+00:00',
            'status' => 'APPROVED',
            'productUuid' => $product->getUuid(),
            'productName' => $product->getName($this->getFirstDomainLocale()),
        ], $data['edges'][0]['node']);
    }

    public function testSummaryAggregatesRatingsWithZeroFilledDistribution(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'productReviews');
        $this->assertSame([
            'averageRating' => 3.44,
            'totalCount' => 9,
            'ratingCounts' => [
                ['rating' => 5, 'count' => 2],
                ['rating' => 4, 'count' => 3],
                ['rating' => 3, 'count' => 2],
                ['rating' => 2, 'count' => 1],
                ['rating' => 1, 'count' => 1],
            ],
        ], $data['summary']);
    }

    public function testReviewsOfVariantFamilyAreReturnedForMainAndVariantUuid(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69', Product::class);
        $variant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148', Product::class);

        $responseForMainVariant = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => $mainVariant->getUuid(),
        ]);
        $responseForVariant = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => $variant->getUuid(),
        ]);

        $dataForMainVariant = $this->getResponseDataForGraphQlType($responseForMainVariant, 'productReviews');
        $dataForVariant = $this->getResponseDataForGraphQlType($responseForVariant, 'productReviews');
        $this->assertSame($dataForMainVariant, $dataForVariant);
        $this->assertSame(1, $dataForMainVariant['totalCount']);

        $reviewOfVariant = $dataForMainVariant['edges'][0]['node'];
        $this->assertSame($variant->getUuid(), $reviewOfVariant['productUuid']);
        $this->assertSame($variant->getName($this->getFirstDomainLocale()), $reviewOfVariant['productName']);
        $this->assertNull($reviewOfVariant['reviewerName'], 'The review is published anonymously, so it must not carry any name.');
    }

    public function testReviewsAreOrderedByRequestedOrderingMode(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        foreach (self::getExpectedRatingsByOrderingMode() as $orderingMode => $expectedRatings) {
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
                'productUuid' => $product->getUuid(),
                'orderingMode' => $orderingMode,
            ]);

            $data = $this->getResponseDataForGraphQlType($response, 'productReviews');
            $this->assertSame($orderingMode, $data['orderingMode']);
            $this->assertSame(
                $expectedRatings,
                array_map(static fn (array $edge) => $edge['node']['rating'], $data['edges']),
                sprintf('Unexpected order of reviews for the "%s" ordering mode.', $orderingMode),
            );
        }
    }

    public function testReviewsArePaginatedByCursor(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $firstPageResponse = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => $product->getUuid(),
            'first' => 5,
        ]);

        $firstPage = $this->getResponseDataForGraphQlType($firstPageResponse, 'productReviews');
        $this->assertSame(9, $firstPage['totalCount']);
        $this->assertCount(5, $firstPage['edges']);
        $this->assertSame(4, $firstPage['edges'][0]['node']['rating']);
        $this->assertTrue($firstPage['pageInfo']['hasNextPage']);

        $secondPageResponse = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => $product->getUuid(),
            'first' => 10,
            'after' => $firstPage['pageInfo']['endCursor'],
        ]);

        $secondPage = $this->getResponseDataForGraphQlType($secondPageResponse, 'productReviews');
        $this->assertCount(4, $secondPage['edges']);
        $this->assertSame(3, $secondPage['edges'][0]['node']['rating']);
        $this->assertFalse($secondPage['pageInfo']['hasNextPage']);
    }

    /**
     * @return array<string, int[]>
     */
    private static function getExpectedRatingsByOrderingMode(): array
    {
        return [
            'NEWEST' => [4, 5, 3, 4, 2, 3, 5, 1, 4],
            'HIGHEST_RATING' => [5, 5, 4, 4, 4, 3, 3, 2, 1],
            'LOWEST_RATING' => [1, 2, 3, 3, 4, 4, 4, 5, 5],
        ];
    }

    public function testPendingAndRejectedReviewsAreNotListedPublicly(): void
    {
        $productWithPendingReviewOnly = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6', Product::class);
        $productWithRejectedReviewOnly = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '9', Product::class);

        foreach ([$productWithPendingReviewOnly, $productWithRejectedReviewOnly] as $product) {
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
                'productUuid' => $product->getUuid(),
            ]);

            $data = $this->getResponseDataForGraphQlType($response, 'productReviews');
            $this->assertSame(0, $data['totalCount']);
            $this->assertSame([], $data['edges']);
            $this->assertSame(0, $data['summary']['totalCount']);
            $this->assertNull($data['summary']['averageRating']);
        }
    }

    public function testUnknownProductReturnsNotFoundError(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductReviewsQuery.graphql', [
            'productUuid' => Uuid::uuid4()->toString(),
        ]);

        $this->assertUserError($response, 'product-not-found', 404);
    }

    public function testCurrentCustomerUserProductReviewsRequireLogin(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CurrentCustomerUserProductReviewsQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        $this->assertResponseContainsArrayOfWarnings($response);
        $this->assertSame('Access denied to this field.', $this->getWarningsFromResponse($response)[0]['message']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\ProductReview;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

final class CurrentCustomerUserProductReviewsTest extends GraphQlWithLoginTestCase
{
    public function testCustomerSeesAllOwnReviewsRegardlessOfStatus(): void
    {
        $data = $this->getOwnReviews();

        $this->assertSame(5, $data['totalCount']);

        $statuses = array_count_values(
            array_map(static fn (array $edge) => $edge['node']['status'], $data['edges']),
        );
        ksort($statuses);
        $this->assertSame([
            'APPROVED' => 2,
            'PENDING' => 2,
            'REJECTED' => 1,
        ], $statuses);
    }

    public function testOwnReviewsArePaginatedNewestFirst(): void
    {
        $firstPage = $this->getOwnReviews(['first' => 2]);

        $this->assertSame(5, $firstPage['totalCount']);
        $this->assertCount(2, $firstPage['edges']);
        $this->assertTrue($firstPage['pageInfo']['hasNextPage']);

        $secondPage = $this->getOwnReviews(['first' => 2, 'after' => $firstPage['pageInfo']['endCursor']]);

        $this->assertCount(2, $secondPage['edges']);

        $createdAtValues = array_map(
            static fn (array $edge) => $edge['node']['createdAt'],
            [...$firstPage['edges'], ...$secondPage['edges']],
        );

        $sortedCreatedAtValues = $createdAtValues;
        rsort($sortedCreatedAtValues);
        $this->assertSame($sortedCreatedAtValues, $createdAtValues, 'Own reviews have to be ordered newest first across pages.');
    }

    public function testCustomerSeesOwnPendingReviewOfProduct(): void
    {
        $productUuid = $this->getProductUuidByReference('6');

        $data = $this->getOwnReviews(['productUuid' => $productUuid]);

        $this->assertSame(1, $data['totalCount']);
        $this->assertSame('PENDING', $data['edges'][0]['node']['status']);
        $this->assertSame(4, $data['edges'][0]['node']['rating']);
        $this->assertSame('Jaromír J.', $data['edges'][0]['node']['reviewerName']);
        $this->assertSame($productUuid, $data['edges'][0]['node']['product']['uuid']);
        $this->assertNotSame('', $data['edges'][0]['node']['product']['slug']);
        $this->assertTrue($data['edges'][0]['node']['product']['isVisible']);
        $this->assertNull($data['edges'][0]['node']['responseText']);
        $this->assertNull($data['edges'][0]['node']['responseCreatedAt']);
        $this->assertSame(1, $data['edges'][0]['node']['rejectedImagesCount']);
    }

    public function testCustomerSeesOwnPendingReviewForFirstProductWithoutVerificationOrResponse(): void
    {
        $data = $this->getOwnReviews(['productUuid' => $this->getProductUuidByReference('1')]);

        $this->assertSame(1, $data['totalCount']);
        $this->assertSame('PENDING', $data['edges'][0]['node']['status']);
        $this->assertNull($data['edges'][0]['node']['rejectionReason']);
        $this->assertSame(0, $data['edges'][0]['node']['rejectedImagesCount']);
        $this->assertFalse($data['edges'][0]['node']['isVerifiedPurchase']);
        $this->assertNull($data['edges'][0]['node']['responseText']);
        $this->assertNull($data['edges'][0]['node']['responseCreatedAt']);
    }

    public function testCustomerSeesOwnRejectedReviewOfProduct(): void
    {
        $data = $this->getOwnReviews(['productUuid' => $this->getProductUuidByReference('9')]);

        $this->assertSame(1, $data['totalCount']);
        $this->assertSame('REJECTED', $data['edges'][0]['node']['status']);
        $this->assertSame(1, $data['edges'][0]['node']['rating']);
        $this->assertSame(
            t(
                'This product is terrible, and anyone who buys it has no idea what they are doing.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            $data['edges'][0]['node']['text'],
        );
        $this->assertSame(
            t(
                'This review contains content that is not suitable for publication.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            $data['edges'][0]['node']['rejectionReason'],
        );
        $this->assertNull($data['edges'][0]['node']['responseText']);
        $this->assertNull($data['edges'][0]['node']['responseCreatedAt']);
    }

    public function testCustomerSeesOwnApprovedReviewWithResponse(): void
    {
        $data = $this->getOwnReviews(['productUuid' => $this->getProductUuidByReference('4')]);

        $this->assertSame(1, $data['totalCount']);
        $this->assertSame('APPROVED', $data['edges'][0]['node']['status']);
        $this->assertSame(
            t(
                'Thank you for your review. We are sorry the product did not fully meet your expectations.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            $data['edges'][0]['node']['responseText'],
        );
        $this->assertSame('2026-05-30T09:00:00+00:00', $data['edges'][0]['node']['responseCreatedAt']);
    }

    public function testOwnAnonymousReviewOfVariantIsReturnedForMainVariantWithMaskedName(): void
    {
        $variant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '148', Product::class);

        $data = $this->getOwnReviews(['productUuid' => $this->getProductUuidByReference('69')]);

        $this->assertSame(1, $data['totalCount']);
        $this->assertSame('APPROVED', $data['edges'][0]['node']['status']);
        $this->assertSame($variant->getUuid(), $data['edges'][0]['node']['productUuid']);
        $this->assertNull($data['edges'][0]['node']['reviewerName'], 'Even the own anonymous review must not carry any name.');
        $this->assertSame(
            t(
                'We are sorry the selected variant did not meet your needs. Our customer care team will be happy to help you choose a more suitable product.',
                [],
                Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                $this->getLocaleForFirstDomain(),
            ),
            $data['edges'][0]['node']['responseText'],
        );
        $this->assertSame('2026-05-29T09:00:00+00:00', $data['edges'][0]['node']['responseCreatedAt']);
    }

    public function testProductReviewedByAnotherCustomerOnlyReturnsEmptyConnection(): void
    {
        $data = $this->getOwnReviews(['productUuid' => $this->getProductUuidByReference('10')]);

        $this->assertSame(0, $data['totalCount']);
        $this->assertSame([], $data['edges']);
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<string, mixed>
     */
    private function getOwnReviews(array $variables = []): array
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/CurrentCustomerUserProductReviewsQuery.graphql',
            $variables,
        );

        return $this->getResponseDataForGraphQlType($response, 'currentCustomerUserProductReviews');
    }

    private function getProductUuidByReference(string $productReferenceName): string
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productReferenceName, Product::class)->getUuid();
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\ProductReview;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

final class CreateProductReviewLoggedMutationTest extends GraphQlWithLoginTestCase
{
    public function testCustomerCannotReviewSameProductTwice(): void
    {
        $alreadyReviewedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $alreadyReviewedProduct->getUuid(),
                'rating' => 5,
                'firstName' => 'Jaromír',
                'lastName' => 'Jágr',
            ],
        ]);

        $this->assertUserError($response, 'duplicate-product-review');
    }

    public function testSpontaneousReviewOfPurchasedProductIsVerifiedAndUsesAccountEmail(): void
    {
        // product 10 was bought by the logged customer in an order whose status allows product reviews
        $purchasedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '10', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $purchasedProduct->getUuid(),
                'rating' => 5,
                'text' => 'Bought it and I am happy with it.',
                'firstName' => 'Jaromír',
                'lastName' => 'Jágr',
                'email' => 'different.email@example.com',
            ],
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'CreateProductReview');
        $this->assertSame('PENDING', $data['status']);
        $this->assertTrue($data['isVerifiedPurchase']);

        $productReview = $this->em->getRepository(ProductReview::class)->findOneBy(['uuid' => $data['uuid']]);
        $this->assertNotNull($productReview);
        $this->assertSame(self::DEFAULT_USER_EMAIL, $productReview->getEmail(), 'The email of a logged in customer comes from the account, not from the input.');
        $this->assertNotNull($productReview->getCustomerUser());
        $this->assertNotNull($productReview->getOrderItem());
        $this->assertSame($purchasedProduct->getId(), $productReview->getOrderItem()->getProduct()->getId());
    }

    public function testProductBoughtOnlyInOrderWithoutReviewsAllowedIsUnverified(): void
    {
        // product 15 was bought by the logged customer in order 2 only, which is in the "new" status
        // that does not carry the reviews flag, so the purchase does not verify the review
        $orderedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '15', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $orderedProduct->getUuid(),
                'rating' => 5,
                'firstName' => 'Jaromír',
                'lastName' => 'Jágr',
            ],
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'CreateProductReview');
        $this->assertFalse($data['isVerifiedPurchase']);

        $productReview = $this->em->getRepository(ProductReview::class)->findOneBy(['uuid' => $data['uuid']]);
        $this->assertNotNull($productReview);
        $this->assertNull($productReview->getOrderItem());
    }
}

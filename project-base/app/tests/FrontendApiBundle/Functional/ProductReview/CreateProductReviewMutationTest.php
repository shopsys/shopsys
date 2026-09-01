<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\ProductReview;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReview;
use Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewStatusEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CreateProductReviewMutationTest extends GraphQlTestCase
{
    public function testGuestCreatesPendingUnverifiedReview(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 4,
                'text' => 'Solid product for the price.',
                'firstName' => 'Guybrush',
                'lastName' => 'Threepwood',
                'email' => 'guybrush@example.com',
            ],
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'CreateProductReview');
        $this->assertSame('PENDING', $data['status']);
        $this->assertSame('Guybrush T.', $data['reviewerName']);
        $this->assertFalse($data['isVerifiedPurchase']);
        $this->assertSame($product->getUuid(), $data['productUuid']);

        $productReview = $this->getProductReviewByUuid($data['uuid']);
        $this->assertSame(ProductReviewStatusEnum::STATUS_PENDING, $productReview->getStatus());
        $this->assertSame('guybrush@example.com', $productReview->getEmail());
        $this->assertNull($productReview->getCustomerUser());
        $this->assertNull($productReview->getOrderItem());
        $this->assertNotSame('', $productReview->getIpAddress());
    }

    public function testGuestReviewLinkedToOrderItemByUrlHashIsVerified(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '15', Product::class);
        $orderItem = $this->getOrderItemOfProduct($order, $product);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 5,
                'firstName' => 'April',
                'lastName' => 'Ryan',
                'email' => 'april.ryan@example.com',
                'orderUrlHash' => $order->getUrlHash(),
            ],
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'CreateProductReview');
        $this->assertTrue($data['isVerifiedPurchase']);

        $productReview = $this->getProductReviewByUuid($data['uuid']);
        $this->assertSame($orderItem->getUuid(), $productReview->getOrderItem()->getUuid());
    }

    public function testGuestCannotReviewSameProductFromTheSameOrderTwice(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '15', Product::class);

        $firstResponse = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 5,
                'firstName' => 'April',
                'lastName' => 'Ryan',
                'email' => 'april.ryan@example.com',
                'orderUrlHash' => $order->getUrlHash(),
            ],
        ]);
        $firstData = $this->getResponseDataForGraphQlType($firstResponse, 'CreateProductReview');
        $this->assertTrue($firstData['isVerifiedPurchase']);

        $secondResponse = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 1,
                'firstName' => 'Zoe',
                'lastName' => 'Castillo',
                'email' => 'zoe.castillo@example.com',
                'orderUrlHash' => $order->getUrlHash(),
            ],
        ]);

        $this->assertUserError($secondResponse, 'duplicate-product-review');
    }

    public function testOrderReturnsUuidsOfProductsReviewedFromTheOrder(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '15', Product::class);

        $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 5,
                'firstName' => 'April',
                'lastName' => 'Ryan',
                'email' => 'april.ryan@example.com',
                'orderUrlHash' => $order->getUrlHash(),
            ],
        ]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderReviewedProductUuidsQuery.graphql', [
            'urlHash' => $order->getUrlHash(),
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'order');
        $this->assertSame([$product->getUuid()], $data['reviewedProductUuids']);
    }

    public function testGuestWithWrongUrlHashCreatesUnverifiedReview(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '15', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 5,
                'firstName' => 'April',
                'lastName' => 'Ryan',
                'email' => 'april.ryan@example.com',
                'orderUrlHash' => 'wrong-url-hash',
            ],
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'CreateProductReview');
        $this->assertFalse($data['isVerifiedPurchase']);

        $this->assertNull($this->getProductReviewByUuid($data['uuid'])->getOrderItem());
    }

    public function testGuestReviewOfProductMissingInTheOrderIsUnverified(): void
    {
        $order = $this->getReferenceForDomain(OrderDataFixture::ORDER_DELIVERED_MONTH_AGO, 1, Order::class);
        $notOrderedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $notOrderedProduct->getUuid(),
                'rating' => 5,
                'firstName' => 'April',
                'lastName' => 'Ryan',
                'email' => 'april.ryan@example.com',
                'orderUrlHash' => $order->getUrlHash(),
            ],
        ]);

        $data = $this->getResponseDataForGraphQlType($response, 'CreateProductReview');
        $this->assertFalse($data['isVerifiedPurchase']);

        $this->assertNull($this->getProductReviewByUuid($data['uuid'])->getOrderItem());
    }

    public function testMainVariantCannotBeReviewed(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '69', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $mainVariant->getUuid(),
                'rating' => 5,
                'firstName' => 'Guybrush',
                'lastName' => 'Threepwood',
                'email' => 'guybrush@example.com',
            ],
        ]);

        $this->assertUserError($response, 'product-review-variant-required');
    }

    public function testGuestWithoutEmailIsRejectedByValidation(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 4,
                'firstName' => 'Guybrush',
                'lastName' => 'Threepwood',
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsFromResponse($response)[0]['extensions']['validation'];
        $this->assertArrayHasKey('input.email', $validationErrors);
    }

    public function testRatingOutOfRangeIsRejectedByValidation(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CreateProductReviewMutation.graphql', [
            'input' => [
                'productUuid' => $product->getUuid(),
                'rating' => 6,
                'firstName' => 'Guybrush',
                'lastName' => 'Threepwood',
                'email' => 'guybrush@example.com',
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $validationErrors = $this->getErrorsFromResponse($response)[0]['extensions']['validation'];
        $this->assertArrayHasKey('input.rating', $validationErrors);
    }

    private function getProductReviewByUuid(string $uuid): ProductReview
    {
        $productReview = $this->em->getRepository(ProductReview::class)->findOneBy(['uuid' => $uuid]);
        $this->assertNotNull($productReview);

        return $productReview;
    }

    private function getOrderItemOfProduct(Order $order, Product $product): OrderItem
    {
        foreach ($order->getProductItems() as $orderItem) {
            if ($orderItem->getProduct() === $product) {
                return $orderItem;
            }
        }

        $this->fail(sprintf('Order %d has no item of product %d.', $order->getId(), $product->getId()));
    }
}

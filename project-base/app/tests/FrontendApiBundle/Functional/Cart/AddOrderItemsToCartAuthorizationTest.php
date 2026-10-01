<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Order\Order;
use App\Model\Product\Product;
use Ramsey\Uuid\Uuid;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AddOrderItemsToCartAuthorizationTest extends GraphQlTestCase
{
    private const string MISSING_PROOF_ERROR_MESSAGE = 'You need to be logged in or provide argument \'orderUrlHash\'.';

    /**
     * @inject
     */
    private CartApiFacade $cartApiFacade;

    public function testAddOrderItemsToCartWithUuidOnlyIsDeniedForAnonymousUser(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9', Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('message', $errors[0]);
        $this->assertSame(self::MISSING_PROOF_ERROR_MESSAGE, $errors[0]['message']);
    }

    public function testAddOrderItemsToCartWithMismatchedUrlHashIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9', Order::class);
        $anotherOrder = $this->getReference(OrderDataFixture::ORDER_PREFIX . '7', Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            ['orderUuid' => $order->getUuid(), 'orderUrlHash' => $anotherOrder->getUrlHash()],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testAddOrderItemsToCartWithUnknownUrlHashIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9', Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            ['orderUuid' => $order->getUuid(), 'orderUrlHash' => 'unknown-url-hash'],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testExistingCartIsKeptWhenAccessToOrderIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9', Order::class);
        $cartUuid = Uuid::uuid4()->toString();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists(null, $cartUuid);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->cartApiFacade->addProductByUuidToCart($product->getUuid(), 4, true, $cart);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'cartUuid' => $cartUuid,
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => 'unknown-url-hash',
                'shouldMerge' => false,
            ],
        );

        $this->assertUserError($response, 'order-not-found');

        $cartResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetCart.graphql',
            ['cartUuid' => $cartUuid],
        );
        $cartData = $this->getResponseDataForGraphQlType($cartResponse, 'cart');

        $this->assertSame(
            [
                [
                    'quantity' => 4,
                    'product' => [
                        'name' => $product->getName($this->getLocaleForFirstDomain()),
                    ],
                ],
            ],
            $cartData['items'],
        );
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class AddOrderItemsToCartAsNonOwnerCustomerUserTest extends GraphQlWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const string DEFAULT_USER_PASSWORD = 'no-reply.3';

    public function testAddOrderItemsToCartByUuidForForeignOrderIsDenied(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            ['orderUuid' => $order->getUuid()],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public function testAddOrderItemsToCartWithValidUrlHashForForeignOrderSucceeds(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            ['orderUuid' => $order->getUuid(), 'orderUrlHash' => $order->getUrlHash()],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');

        $this->assertNotEmpty($data['items']);
    }
}

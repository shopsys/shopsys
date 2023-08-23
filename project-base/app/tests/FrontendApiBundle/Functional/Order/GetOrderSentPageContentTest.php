<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\FrontendApi\Model\Order\OrderFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetOrderSentPageContentTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testGetOrderSentPageContent(): void
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 1,
        ]);

        $cartUuid = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['uuid'];

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangeTransportInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'transportUuid' => $transport->getUuid(),
        ]);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/ChangePaymentInCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'paymentUuid' => $transport->getPayments()[0]->getUuid(),
        ]);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/CreateOrderMutation.graphql', [
            'cartUuid' => $cartUuid,
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'email' => 'user@example.com',
            'telephone' => '+53 123456789',
            'onCompanyBehalf' => false,
            'street' => '123 Fake Street',
            'city' => 'Springfield',
            'postcode' => '12345',
            'country' => 'CZ',
            'differentDeliveryAddress' => false,
        ]);

        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/OrderSentPageContentQuery.graphql', [
            'orderUuid' => $orderUuid,
        ]);

        $this->assertEquals(
            $this->orderFacade->getOrderSentPageContent($orderUuid),
            $response['data']['orderSentPageContent'],
        );
    }

    public function testGetOrderSentPageContentForNonExistingOrder(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/OrderSentPageContentQuery.graphql', [
            'orderUuid' => '4c0e44a5-74fc-4df3-b868-c4900b36adbf',
        ]);

        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals("Order with UUID '4c0e44a5-74fc-4df3-b868-c4900b36adbf' not found.", $errors[0]['message']);
    }
}

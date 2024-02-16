<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\PaymentDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade;
use App\Model\Payment\Payment;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetOrderSentPageContentTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private OrderContentPageFacade $orderContentPageFacade;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    public function testGetOrderSentPageContent(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);

        $order = $this->createOrder($product, $transport, $transport->getPayments()[0]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $this->assertEquals(
            $this->orderContentPageFacade->getOrderSentPageContent($order),
            $response['data']['orderSentPageContent'],
        );
    }

    public function testGetPaymentSuccessfulPageContents(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::FIRST_DOMAIN_ID);

        $order = $this->createOrder($product, $transport, $payment);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentSuccessfulPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $errors = $this->getErrorsFromResponse($response);

        $this->assertEquals(
            'order-sent-page-not-available',
            $errors[0]['extensions']['userCode'],
        );

        $order = $this->orderFacade->getByUuid($order->getUuid());
        $order->setOrderPaymentStatusPageValidFromNow();
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentSuccessfulPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $this->assertEquals(
            $this->orderContentPageFacade->getPaymentSuccessfulPageContent($order),
            $response['data']['orderPaymentSuccessfulContent'],
        );
    }

    public function testGetPaymentFailedPageContents(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL);
        /** @var \App\Model\Payment\Payment $payment */
        $payment = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::FIRST_DOMAIN_ID);

        $order = $this->createOrder($product, $transport, $payment);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentFailedPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $errors = $this->getErrorsFromResponse($response);

        $this->assertEquals(
            'order-sent-page-not-available',
            $errors[0]['extensions']['userCode'],
        );

        $order = $this->orderFacade->getByUuid($order->getUuid());
        $order->setOrderPaymentStatusPageValidFromNow();
        $this->em->flush();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PaymentFailedPageContentQuery.graphql', [
            'orderUuid' => $order->getUuid(),
        ]);

        $this->assertEquals(
            $this->orderContentPageFacade->getPaymentFailedPageContent($order),
            $response['data']['orderPaymentFailedContent'],
        );
    }

    public function testGetOrderSentPageContentForNonExistingOrder(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderSentPageContentQuery.graphql', [
            'orderUuid' => '4c0e44a5-74fc-4df3-b868-c4900b36adbf',
        ]);

        $errors = $this->getErrorsFromResponse($response);

        self::assertEquals("Order with UUID '4c0e44a5-74fc-4df3-b868-c4900b36adbf' not found.", $errors[0]['message']);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Payment\Payment $payment
     * @return \App\Model\Order\Order
     */
    private function createOrder(Product $product, Transport $transport, Payment $payment): Order
    {
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
            'paymentUuid' => $payment->getUuid(),
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

        return $this->orderFacade->getByUuid($orderUuid);
    }
}

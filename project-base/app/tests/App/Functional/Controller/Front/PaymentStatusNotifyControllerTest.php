<?php

declare(strict_types=1);

namespace Tests\App\Functional\Controller\Front;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentTransactionDataFixture;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Test\ApplicationTestCase;
use Tests\FrontendApiBundle\Functional\Payment\GoPay\GoPayClient;

final class PaymentStatusNotifyControllerTest extends ApplicationTestCase
{
    /**
     * @inject
     */
    private PaymentTransactionFacade $paymentTransactionFacade;

    /**
     * @inject
     */
    private PaymentTransactionDataFactory $paymentTransactionDataFactory;

    public function testNotificationMatchingOrderTransactionUpdatesPaymentStatus(): void
    {
        $order = $this->getGoPayOrder();
        $this->assertFalse($order->isPaid());

        $response = $this->notify($order->getUuid(), PaymentTransactionDataFixture::TIMEOUTED_GOPAY_PAYMENT_ID);

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(['status' => 'ok'], json_decode((string)$response->getContent(), true, 512, JSON_THROW_ON_ERROR));
        $this->em->refresh($order);
        $this->assertTrue($order->isPaid());
        $this->assertSame(PaymentStatus::PAID, $order->getLastGoPayTransaction()?->getExternalPaymentStatus());
    }

    public function testGoPayApiFailureIsReportedAsTemporaryErrorWithoutMarkingOrderAsPaid(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $this->createPaymentTransactionFailingOnGoPayApi($order);

        $response = $this->notify($order->getUuid(), GoPayClient::PAYMENT_ID_WITH_API_FAILURE);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->em->refresh($order);
        $this->assertFalse($order->isPaid());
    }

    public function testGoPayApiFailureRollsBackTransactionsUpdatedBeforeTheFailure(): void
    {
        $order = $this->getGoPayOrder();
        $this->createPaymentTransactionFailingOnGoPayApi($order);

        $response = $this->notify($order->getUuid(), PaymentTransactionDataFixture::TIMEOUTED_GOPAY_PAYMENT_ID);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->em->clear();
        $order = $this->getGoPayOrder();
        $this->assertFalse($order->isPaid());
        $this->assertSame(PaymentStatus::TIMEOUTED, $this->getPaymentTransactionByExternalPaymentIdentifier($order, PaymentTransactionDataFixture::TIMEOUTED_GOPAY_PAYMENT_ID)->getExternalPaymentStatus());
    }

    public function testNotificationWithUnknownPaymentIdentifierIsRejected(): void
    {
        $order = $this->getGoPayOrder();

        $response = $this->notify($order->getUuid(), 'unknown');

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->em->refresh($order);
        $this->assertFalse($order->isPaid());
    }

    public function testNotificationWithoutPaymentIdentifierIsRejected(): void
    {
        $response = $this->notify($this->getGoPayOrder()->getUuid(), null);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testNotificationWithMalformedOrderIdentifierIsRejected(): void
    {
        $response = $this->notify('not-a-uuid', PaymentTransactionDataFixture::TIMEOUTED_GOPAY_PAYMENT_ID);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testNotificationForUnknownOrderIsRejected(): void
    {
        $response = $this->notify('11111111-2222-4333-8444-555555555555', PaymentTransactionDataFixture::TIMEOUTED_GOPAY_PAYMENT_ID);

        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    private function notify(string $orderIdentifier, ?string $externalPaymentIdentifier): Response
    {
        $parameters = ['orderIdentifier' => $orderIdentifier];

        if ($externalPaymentIdentifier !== null) {
            $parameters['id'] = $externalPaymentIdentifier;
        }

        $this->configureCurrentClient(null, null);
        self::getCurrentClient()->request(
            'GET',
            $this->getLocalizedPathOnFirstDomainByRouteName('front_order_payment_status_notify', $parameters),
        );

        return self::getCurrentClient()->getResponse();
    }

    private function createPaymentTransactionFailingOnGoPayApi(Order $order): void
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();
        $paymentTransactionData->externalPaymentIdentifier = GoPayClient::PAYMENT_ID_WITH_API_FAILURE;
        $paymentTransactionData->externalPaymentStatus = PaymentStatus::CREATED;
        $this->paymentTransactionFacade->create($paymentTransactionData);
    }

    private function getPaymentTransactionByExternalPaymentIdentifier(Order $order, string $externalPaymentIdentifier): PaymentTransaction
    {
        $paymentTransactions = $order->getPaymentTransactions();

        foreach ($paymentTransactions as $paymentTransaction) {
            if ($paymentTransaction->getExternalPaymentIdentifier() === $externalPaymentIdentifier) {
                return $paymentTransaction;
            }
        }

        $this->fail(sprintf('The order has no payment transaction "%s".', $externalPaymentIdentifier));
    }

    private function getGoPayOrder(): Order
    {
        return $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_14, Order::class);
    }
}

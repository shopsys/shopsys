<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\DataFixtures\Demo\OrderDataFixture;
use App\Model\Order\Order;
use GoPay\Definition\Response\PaymentStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\ContentPage\OrderContentPageFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFactory;
use Shopsys\FrontendApiBundle\Model\Order\ConfirmationPageContent\ConfirmationPageContentStatusEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class GetOrderConfirmationPageContentTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private OrderContentPageFacade $orderContentPageFacade;

    /**
     * @inject
     */
    private PaymentTransactionDataFactory $paymentTransactionDataFactory;

    /**
     * @inject
     */
    private PaymentTransactionFactory $paymentTransactionFactory;

    public function testOrderConfirmationPageContentForOrderWithoutGatewayPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '3', Order::class);

        $this->assertOrderConfirmationPageContent(
            $order,
            ConfirmationPageContentStatusEnum::STATUS_SUCCESSFUL,
            $this->orderContentPageFacade->getOrderSentPageContent($order),
        );
    }

    #[DataProvider('gatewayPaymentStatusesDataProvider')]
    public function testOrderConfirmationPageContentForGatewayPayment(
        string $expectedConfirmationPageStatus,
        string $paymentTransactionStatus,
        string $getPageContentMethodName,
    ): void {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);

        $paymentTransaction = $this->createPaymentTransaction($order, $paymentTransactionStatus);
        $order->addPaymentTransaction($paymentTransaction);
        $this->em->flush();

        $this->assertOrderConfirmationPageContent(
            $order,
            $expectedConfirmationPageStatus,
            $this->orderContentPageFacade->{$getPageContentMethodName}($order),
        );
    }

    public static function gatewayPaymentStatusesDataProvider(): iterable
    {
        yield 'failed' => [
            ConfirmationPageContentStatusEnum::STATUS_FAILED,
            PaymentStatus::CANCELED,
            'getPaymentFailedPageContent',
        ];

        yield 'in_process' => [
            ConfirmationPageContentStatusEnum::STATUS_IN_PROCESS,
            PaymentStatus::PAYMENT_METHOD_CHOSEN,
            'getPaymentInProcessPageContent',
        ];

        yield 'successful' => [
            ConfirmationPageContentStatusEnum::STATUS_SUCCESSFUL,
            PaymentStatus::PAID,
            'getPaymentSuccessfulPageContent',
        ];
    }

    private function assertOrderConfirmationPageContent(
        Order $order,
        string $expectedStatus,
        string $expectedContent,
    ): void {
        $responseData = $this->getOrderConfirmationPageContentResponseData($order);

        $this->assertSame(strtoupper($expectedStatus), $responseData['status']);
        $this->assertSame($expectedContent, $responseData['content']);
    }

    /**
     * @return array{content: string, status: string}
     */
    private function getOrderConfirmationPageContentResponseData(Order $order): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderConfirmationPageContentQuery.graphql', [
            'urlHash' => $order->getUrlHash(),
        ]);
        $orderData = $this->getResponseDataForGraphQlType($response, 'order');

        return $orderData['confirmationPageContent'];
    }

    private function createPaymentTransaction(Order $order, string $externalStatus): PaymentTransaction
    {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->paidAmount = $order->getTotalPriceWithVat();
        $paymentTransactionData->externalPaymentIdentifier = (string)random_int(11111, 99999);
        $paymentTransactionData->externalPaymentStatus = $externalStatus;

        $paymentTransaction = $this->paymentTransactionFactory->create($paymentTransactionData);
        $this->em->persist($paymentTransaction);

        return $paymentTransaction;
    }
}

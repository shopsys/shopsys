<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderPaymentsTest extends GraphQlTestCase
{
    public function testOrderPaymentsPricesWhenFreePriceLimitIsMet(): void
    {
        // make sure the payment and transport is free
        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($this->domain->getId(), Money::create(1));

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsPricesQuery.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );

        $orderPaymentsResponse = $this->getResponseDataForGraphQlType($response, 'orderPayments');

        foreach ($orderPaymentsResponse['availablePayments'] as $paymentData) {
            $this->assertSame($this->moneyFormatterHelper->formatWithMaxFractionDigits(Money::zero()), $paymentData['price']['priceWithoutVat']);
        }
    }

    #[DataProvider('getOrderPaymentsMultidomainDataProvider')]
    #[Group('multidomain')]
    public function testGetOrderPaymentsMultidomain(
        string $orderReferenceName,
        string $expectedCurrentPaymentReferenceName,
        array $expectedAvailablePaymentReferenceNames,
    ): void {
        $this->assertOrderPayments($orderReferenceName, $expectedCurrentPaymentReferenceName, $expectedAvailablePaymentReferenceNames);
    }

    #[DataProvider('getOrderPaymentsSingledomainDataProvider')]
    #[Group('singledomain')]
    public function testGetOrderPaymentsSingledomain(
        string $orderReferenceName,
        string $expectedCurrentPaymentReferenceName,
        array $expectedAvailablePaymentReferenceNames,
    ): void {
        $this->assertOrderPayments($orderReferenceName, $expectedCurrentPaymentReferenceName, $expectedAvailablePaymentReferenceNames);
    }

    public static function getOrderPaymentsMultidomainDataProvider(): iterable
    {
        yield from static::getOrderPaymentsSingledomainDataProvider();
    }

    #[Group('multidomain')]
    public function testGetOrderPaymentsForOrderFromAnotherDomainIsDenied(): void
    {
        $orderOnSecondDomain = $this->getReference(OrderDataFixture::ORDER_PREFIX . 24, Order::class);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            [
                'orderUuid' => $orderOnSecondDomain->getUuid(),
                'orderUrlHash' => $orderOnSecondDomain->getUrlHash(),
            ],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    public static function getOrderPaymentsSingledomainDataProvider(): iterable
    {
        yield 'order with personal collection transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 1,
            'expectedCurrentPaymentReferenceName' => PaymentDataFixture::PAYMENT_GOPAY_CARD,
            'expectedAvailablePaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT,
                PaymentDataFixture::PAYMENT_CARD,
                PaymentDataFixture::PAYMENT_CASH,
            ],
        ];

        yield 'order with Czech post transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 3,
            'expectedCurrentPaymentReferenceName' => PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            'expectedAvailablePaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT,
                PaymentDataFixture::PAYMENT_BANK_TRANSFER,
            ],
        ];
    }

    public function testGetOrderPaymentsThrowsUserErrorWithNonExistingOrderUuid(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            [
                'orderUuid' => '00000000-0000-0000-0000-000000000000',
                'orderUrlHash' => 'non-existing-url-hash',
            ],
        );

        $this->assertUserError($response, 'order-not-found');
    }

    /**
     * @param string[] $expectedPaymentReferenceNames
     * @return array{array{uuid: string, name: string}}
     */
    private function getExpectedPaymentsResponse(array $expectedPaymentReferenceNames): array
    {
        return array_map(
            fn (string $paymentReferenceName) => $this->getExpectedPaymentResponse($paymentReferenceName),
            $expectedPaymentReferenceNames,
        );
    }

    private function assertOrderPayments(
        string $orderReferenceName,
        string $expectedCurrentPaymentReferenceName,
        array $expectedAvailablePaymentReferenceNames,
    ): void {
        $order = $this->getReference($orderReferenceName, Order::class);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'orderUrlHash' => $order->getUrlHash(),
            ],
        );
        $this->assertSame(
            $this->getExpectedPaymentsResponse($expectedAvailablePaymentReferenceNames),
            $this->getResponseDataForGraphQlType($response, 'orderPayments')['availablePayments'],
        );
        $this->assertSame(
            $this->getExpectedPaymentResponse($expectedCurrentPaymentReferenceName),
            $this->getResponseDataForGraphQlType($response, 'orderPayments')['currentPayment'],
        );
    }

    /**
     * @return array{uuid: string, name: string}
     */
    private function getExpectedPaymentResponse(string $paymentReferenceName): array
    {
        $payment = $this->getReference($paymentReferenceName, Payment::class);

        return ['uuid' => $payment->getUuid(), 'name' => $payment->getName($this->getFirstDomainLocale())];
    }
}

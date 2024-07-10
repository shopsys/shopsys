<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class OrderPaymentsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private PricingSetting $pricingSetting;

    public function testOrderPaymentsPricesWhenFreePriceLimitIsMet(): void
    {
        // make sure the payment and transport is free
        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($this->domain->getId(), Money::create(1));

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsPricesQuery.graphql',
            [
                'orderUuid' => $order->getUuid(),
            ],
        );

        $orderPaymentsResponse = $this->getResponseDataForGraphQlType($response, 'orderPayments');

        foreach ($orderPaymentsResponse['availablePayments'] as $paymentData) {
            $this->assertSame(MoneyFormatterHelper::formatWithMaxFractionDigits(Money::zero()), $paymentData['price']['priceWithoutVat']);
        }
    }

    /**
     * @param string $orderReferenceName
     * @param string $expectedCurrentPaymentReferenceName
     * @param array $expectedAvailablePaymentReferenceNames
     */
    #[DataProvider('getOrderPaymentsMultidomainDataProvider')]
    #[Group('multidomain')]
    public function testGetOrderPaymentsMultidomain(
        string $orderReferenceName,
        string $expectedCurrentPaymentReferenceName,
        array $expectedAvailablePaymentReferenceNames,
    ): void {
        $this->assertOrderPayments($orderReferenceName, $expectedCurrentPaymentReferenceName, $expectedAvailablePaymentReferenceNames);
    }

    /**
     * @param string $orderReferenceName
     * @param string $expectedCurrentPaymentReferenceName
     * @param array $expectedAvailablePaymentReferenceNames
     */
    #[DataProvider('getOrderPaymentsSingledomainDataProvider')]
    #[Group('singledomain')]
    public function testGetOrderPaymentsSingledomain(
        string $orderReferenceName,
        string $expectedCurrentPaymentReferenceName,
        array $expectedAvailablePaymentReferenceNames,
    ): void {
        $this->assertOrderPayments($orderReferenceName, $expectedCurrentPaymentReferenceName, $expectedAvailablePaymentReferenceNames);
    }

    /**
     * @return iterable
     */
    public static function getOrderPaymentsMultidomainDataProvider(): iterable
    {
        yield from static::getOrderPaymentsSingledomainDataProvider();

        yield 'order on second domain with dron delivery transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 24,
            'expectedCurrentPaymentReferenceName' => PaymentDataFixture::PAYMENT_LATER,
            'expectedAvailablePaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::SECOND_DOMAIN_ID,
            ],
        ];
    }

    /**
     * @return iterable
     */
    public static function getOrderPaymentsSingledomainDataProvider(): iterable
    {
        yield 'order with personal collection transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 1,
            'expectedCurrentPaymentReferenceName' => PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::FIRST_DOMAIN_ID,
            'expectedAvailablePaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_CARD,
                PaymentDataFixture::PAYMENT_CASH,
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::FIRST_DOMAIN_ID,
            ],
        ];

        yield 'order with Czech post transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 3,
            'expectedCurrentPaymentReferenceName' => PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
            'expectedAvailablePaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::FIRST_DOMAIN_ID,
            ],
        ];
    }

    public function testGetOrderPaymentsThrowsUserErrorWithNonExistingOrderUuid(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            [
                'orderUuid' => '00000000-0000-0000-0000-000000000000',
            ],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        $this->assertArrayHasKey(0, $errors);
        $this->assertArrayHasKey('extensions', $errors[0]);
        $this->assertArrayHasKey('userCode', $errors[0]['extensions']);
        $this->assertSame(
            'order-not-found',
            $errors[0]['extensions']['userCode'],
        );
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

    /**
     * @param string $orderReferenceName
     * @param string $expectedCurrentPaymentReferenceName
     * @param array $expectedAvailablePaymentReferenceNames
     */
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
     * @param string $paymentReferenceName
     * @return array{uuid: string, name: string}
     */
    private function getExpectedPaymentResponse(string $paymentReferenceName): array
    {
        $payment = $this->getReference($paymentReferenceName, Payment::class);

        return ['uuid' => $payment->getUuid(), 'name' => $payment->getName($this->getFirstDomainLocale())];
    }
}

<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrontendApiBundle\Component\Price\MoneyFormatterHelper;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;
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

        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsPricesQuery.graphql',
            [
                'orderUuid' => $order->getUuid(),
            ],
        );

        foreach ($this->getResponseDataForGraphQlType($response, 'orderPayments') as $paymentData) {
            $this->assertSame(MoneyFormatterHelper::formatWithMaxFractionDigits(Money::zero()), $paymentData['price']['priceWithoutVat']);
        }
    }

    /**
     * @group multidomain
     * @dataProvider getOrderPaymentsMultidomainDataProvider
     * @param string $orderReferenceName
     * @param array $expectedPaymentReferenceNames
     */
    public function testGetOrderPaymentsMultidomain(
        string $orderReferenceName,
        array $expectedPaymentReferenceNames,
    ): void {
        $this->assertOrderPayments($orderReferenceName, $expectedPaymentReferenceNames);
    }

    /**
     * @group singledomain
     * @dataProvider getOrderPaymentsSingledomainDataProvider
     * @param string $orderReferenceName
     * @param array $expectedPaymentReferenceNames
     */
    public function testGetOrderPaymentsSingledomain(
        string $orderReferenceName,
        array $expectedPaymentReferenceNames,
    ): void {
        $this->assertOrderPayments($orderReferenceName, $expectedPaymentReferenceNames);
    }

    /**
     * @return iterable
     */
    public function getOrderPaymentsMultidomainDataProvider(): iterable
    {
        yield from $this->getOrderPaymentsSingledomainDataProvider();

        yield 'order on second domain with dron delivery transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 24,
            'expectedPaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::SECOND_DOMAIN_ID,
                PaymentDataFixture::PAYMENT_LATER,
            ],
        ];
    }

    /**
     * @return iterable
     */
    public function getOrderPaymentsSingledomainDataProvider(): iterable
    {
        yield 'order with personal collection transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 1,
            'expectedPaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_CARD,
                PaymentDataFixture::PAYMENT_CASH,
                PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::FIRST_DOMAIN_ID,
                PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::FIRST_DOMAIN_ID,
            ],
        ];

        yield 'order with Czech post transport' => [
            'orderReferenceName' => OrderDataFixture::ORDER_PREFIX . 3,
            'expectedPaymentReferenceNames' => [
                PaymentDataFixture::PAYMENT_CASH_ON_DELIVERY,
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
            OrderNotFoundUserError::CODE,
            $errors[0]['extensions']['userCode'],
        );
    }

    /**
     * @param string[] $expectedPaymentReferenceNames
     * @return array{array{uuid: string, name: string}}
     */
    private function getExpectedPaymentsResponse(array $expectedPaymentReferenceNames): array
    {
        /** @var \App\Model\Payment\Payment[] $expectedPayments */
        $expectedPayments = array_map(
            fn (string $expectedPaymentReferenceName) => $this->getReference($expectedPaymentReferenceName),
            $expectedPaymentReferenceNames,
        );

        return array_map(
            fn (Payment $expectedPayment) => ['uuid' => $expectedPayment->getUuid(), 'name' => $expectedPayment->getName($this->getFirstDomainLocale())],
            $expectedPayments,
        );
    }

    /**
     * @param string $orderReferenceName
     * @param array $expectedPaymentReferenceNames
     */
    private function assertOrderPayments(string $orderReferenceName, array $expectedPaymentReferenceNames): void
    {
        /** @var \App\Model\Order\Order $order */
        $order = $this->getReference($orderReferenceName);
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OrderPaymentsQuery.graphql',
            [
                'orderUuid' => $order->getUuid(),
            ],
        );
        $this->assertSame(
            $this->getExpectedPaymentsResponse($expectedPaymentReferenceNames),
            $this->getResponseDataForGraphQlType($response, 'orderPayments'),
        );
    }
}

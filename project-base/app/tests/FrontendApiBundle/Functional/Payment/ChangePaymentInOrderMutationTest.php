<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\GoPayDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentInExistingOrder;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ChangePaymentInOrderMutationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private PaymentTransactionDataFactory $paymentTransactionDataFactory;

    /**
     * @inject
     */
    private PaymentTransactionFacade $paymentTransactionFacade;

    /**
     * @inject
     */
    private PricingSetting $pricingSetting;

    public function testChangePaymentInOrderRespectsFreeTransportSetting(): void
    {
        // make sure the payment and transport is free
        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($this->domain->getId(), Money::create(1));

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentCreditCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->assertGreaterThan(Money::zero(), $paymentCreditCard->getPrice($this->domain->getId())->getPrice());

        $expectedTotalPrice = $order->getTotalPriceWithoutVat()->getAmount();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentCreditCard->getUuid(),
            ],
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePaymentInOrder');

        $this->assertSame($expectedTotalPrice, $responseData['totalPrice']['priceWithoutVat']);
    }

    public function testChangePaymentInOrderMutation(): void
    {
        $swiftForFirstDomain = sprintf(GoPayDataFixture::AIRBANK_SWIFT_PATTERN, $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale());

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::FIRST_DOMAIN_ID, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentGoPayBankAccount->getUuid(),
                'paymentGoPayBankSwift' => $swiftForFirstDomain,
            ],
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePaymentInOrder');

        $this->assertSame($paymentGoPayBankAccount->getUuid(), $responseData['payment']['uuid']);
        $this->assertSame($paymentGoPayBankAccount->getName(), $responseData['payment']['name']);
    }

    public function testChangePaymentInOrderMutationNonExistingOrder(): void
    {
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::FIRST_DOMAIN_ID, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => '00000000-0000-0000-0000-000000000000',
                'paymentUuid' => $paymentGoPayBankAccount->getUuid(),
            ],
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $extensions = $errors[0]['extensions'];

        self::assertSame('order-not-found', $extensions['userCode']);
    }

    public function testChangePaymentInOrderMutationNonExistingPayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => '00000000-0000-0000-0000-000000000000',
            ],
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $extensions = $errors[0]['extensions'];

        self::assertSame('payment-not-found', $extensions['userCode']);
    }

    /**
     * @group multidomain
     */
    public function testChangePaymentInOrderValidationUnavailablePayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentGoPayOnSecondDomain = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_DOMAIN . Domain::SECOND_DOMAIN_ID, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentGoPayOnSecondDomain->getUuid(),
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertSame(PaymentInExistingOrder::UNAVAILABLE_PAYMENT_ERROR, $violations['input'][0]['code']);
    }

    public function testChangePaymentInOrderValidationInvalidPaymentTransportCombination(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentLater = $this->getReference(PaymentDataFixture::PAYMENT_LATER, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentLater->getUuid(),
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertSame(PaymentInExistingOrder::UNAVAILABLE_PAYMENT_ERROR, $violations['input'][0]['code']);
    }

    public function testChangePaymentInOrderValidationAlreadyPaidGoPayOrder(): void
    {
        // set transaction as paid
        $paymentTransaction = $this->paymentTransactionFacade->getById(1);
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
        $paymentTransactionData->externalPaymentStatus = PaymentStatus::PAID;
        $this->paymentTransactionFacade->edit(1, $paymentTransactionData);
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentLater = $this->getReference(PaymentDataFixture::PAYMENT_LATER, Payment::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentLater->getUuid(),
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertSame(PaymentInExistingOrder::UNCHANGEABLE_PAYMENT_ERROR, $violations['input'][0]['code']);
    }

    public function testChangePaymentInOrderValidationNotGoPayOrder(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . 2, Order::class);
        $paymentLater = $this->getReference(PaymentDataFixture::PAYMENT_LATER, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentLater->getUuid(),
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertSame(PaymentInExistingOrder::UNCHANGEABLE_PAYMENT_ERROR, $violations['input'][0]['code']);
    }

    public function testChangePaymentInOrderValidationNonExistingSwift(): void
    {
        $this->testInvalidSwift('non-existing-swift');
    }

    /**
     * @group multidomain
     */
    public function testChangePaymentInOrderValidationSwiftForAnotherDomain(): void
    {
        $swiftForSecondDomain = sprintf(GoPayDataFixture::AIRBANK_SWIFT_PATTERN, $this->domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID)->getLocale());

        $this->testInvalidSwift($swiftForSecondDomain);
    }

    /**
     * @param string $swift
     */
    private function testInvalidSwift(string $swift): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT_DOMAIN . Domain::FIRST_DOMAIN_ID, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentGoPayBankAccount->getUuid(),
                'paymentGoPayBankSwift' => $swift,
            ],
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertSame(PaymentInExistingOrder::INVALID_PAYMENT_SWIFT_ERROR, $violations['input'][0]['code']);
    }
}

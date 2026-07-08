<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Payment;

use App\DataFixtures\Demo\GoPayDataFixture;
use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\PaymentDataFixture;
use App\Model\Order\Order;
use App\Model\Payment\Payment;
use GoPay\Definition\Response\PaymentStatus;
use PHPUnit\Framework\Attributes\Group;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
use Shopsys\FrontendApiBundle\Component\Constraints\PaymentInExistingOrder;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ChangePaymentInOrderMutationTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade;

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
    private PaymentFacade $paymentFacade;

    /**
     * @inject
     */
    private PaymentDataFactory $paymentDataFactory;

    /**
     * @inject
     */
    private OrderFacade $orderFacade;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    public function testChangePaymentInOrderRespectsFreeTransportSetting(): void
    {
        // make sure the payment and transport is free
        $this->freeTransportAndPaymentFacade->setPriceLimits(
            $this->domain->getId(),
            [$this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId())->getCode() => Money::create(1)],
        );

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentCreditCard = $this->getReference(PaymentDataFixture::PAYMENT_CARD, Payment::class);
        $this->assertGreaterThan(Money::zero(), $paymentCreditCard->getPrice($this->domain->getId(), $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()))->getPrice());

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
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentGoPayBankAccount->getUuid(),
                'paymentGoPayBankSwift' => $swiftForFirstDomain,
            ],
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePaymentInOrder');

        $paymentItem = $this->findPaymentItem($responseData['items']);
        $this->assertSame($paymentGoPayBankAccount->getUuid(), $paymentItem['payment']['uuid']);
        $this->assertSame($paymentGoPayBankAccount->getName(), $paymentItem['payment']['name']);
    }

    public function testChangePaymentInOrderMutationNonExistingOrder(): void
    {
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => '00000000-0000-0000-0000-000000000000',
                'paymentUuid' => $paymentGoPayBankAccount->getUuid(),
            ],
        ]);

        $this->assertUserError($response, 'order-not-found');
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

        $this->assertUserError($response, 'payment-not-found');
    }

    #[Group('multidomain')]
    public function testChangePaymentInOrderValidationUnavailablePayment(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentGoPay = $this->getReference(PaymentDataFixture::PAYMENT_LATER, Payment::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $paymentGoPay->getUuid(),
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

    #[Group('multidomain')]
    public function testChangePaymentInOrderValidationSwiftForAnotherDomain(): void
    {
        $swiftForSecondDomain = sprintf(GoPayDataFixture::AIRBANK_SWIFT_PATTERN, $this->domain->getDomainConfigById(Domain::SECOND_DOMAIN_ID)->getLocale());

        $this->testInvalidSwift($swiftForSecondDomain);
    }

    private function testInvalidSwift(string $swift): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $paymentGoPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);
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

    public function testChangePaymentInOrderCreatesRoundingItem(): void
    {
        if ($this->getFirstDomainCurrency()->getRoundingType() === Currency::ROUNDING_TYPE_INTEGER) {
            $this->markTestSkipped('Rounding item cannot be created for currencies that already round to whole numbers');
        }

        $order = $this->getReference(OrderDataFixture::ORDER_WITH_GOPAY_PAYMENT_1, Order::class);
        $goPayBankAccount = $this->getReference(PaymentDataFixture::PAYMENT_GOPAY_BANK_ACCOUNT, Payment::class);

        $paymentData = $this->paymentDataFactory->createFromPayment($goPayBankAccount);
        $paymentData->orderRoundingTypeByDomainId[Domain::FIRST_DOMAIN_ID] = OrderRoundingTypeEnum::WHOLE;
        $this->paymentFacade->edit($goPayBankAccount, $paymentData);

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->currencyRoundingType = Currency::ROUNDING_TYPE_HUNDREDTHS;
        $this->orderFacade->edit($order->getId(), $orderData);
        $this->em->clear();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ChangePaymentInOrderMutation.graphql', [
            'input' => [
                'orderUuid' => $order->getUuid(),
                'paymentUuid' => $goPayBankAccount->getUuid(),
            ],
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'ChangePaymentInOrder');

        $roundingItems = array_filter(
            $responseData['items'],
            static fn (array $item) => $item['type'] === OrderItemTypeEnum::TYPE_ROUNDING,
        );
        $this->assertNotEmpty($roundingItems);
    }

    private function findPaymentItem(array $items): array
    {
        foreach ($items as $item) {
            if ($item['type'] === 'payment') {
                return $item;
            }
        }

        $this->fail('Payment item not found in order items');
    }
}

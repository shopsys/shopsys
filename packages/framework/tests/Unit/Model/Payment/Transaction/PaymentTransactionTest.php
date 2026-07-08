<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment\Transaction;

use GoPay\Definition\Response\PaymentStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFactory;

class PaymentTransactionTest extends TestCase
{
    public function testPaymentTransactionWithoutPaymentIsNotRefundable(): void
    {
        $entityNameResolver = new EntityNameResolver([]);
        $paymentTransaction = new PaymentTransactionFactory($entityNameResolver)->create(
            $this->createPaymentTransactionData(null, PaymentStatus::PAID),
        );

        $this->assertFalse($paymentTransaction->isRefundable());
        $this->assertFalse($paymentTransaction->isPartiallyRefunded());
    }

    #[DataProvider('getRefundablePaymentTransactionData')]
    public function testPaymentTransactionRefundability(
        string $paymentType,
        ?string $externalPaymentStatus,
        bool $expectedRefundable,
    ): void {
        $entityNameResolver = new EntityNameResolver([]);
        $paymentTransaction = new PaymentTransactionFactory($entityNameResolver)->create(
            $this->createPaymentTransactionData($this->createPayment($paymentType), $externalPaymentStatus),
        );

        $this->assertSame($expectedRefundable, $paymentTransaction->isRefundable());
    }

    /**
     * @return iterable<string, array{paymentType: string, externalPaymentStatus: string|null, expectedRefundable: bool}>
     */
    public static function getRefundablePaymentTransactionData(): iterable
    {
        yield 'GoPay paid transaction is refundable' => [
            'paymentType' => PaymentTypeEnum::TYPE_GOPAY,
            'externalPaymentStatus' => PaymentStatus::PAID,
            'expectedRefundable' => true,
        ];

        yield 'GoPay partially refunded transaction is refundable' => [
            'paymentType' => PaymentTypeEnum::TYPE_GOPAY,
            'externalPaymentStatus' => PaymentStatus::PARTIALLY_REFUNDED,
            'expectedRefundable' => true,
        ];

        yield 'GoPay unpaid transaction is not refundable' => [
            'paymentType' => PaymentTypeEnum::TYPE_GOPAY,
            'externalPaymentStatus' => PaymentStatus::CREATED,
            'expectedRefundable' => false,
        ];

        yield 'GoPay transaction without status is not refundable' => [
            'paymentType' => PaymentTypeEnum::TYPE_GOPAY,
            'externalPaymentStatus' => null,
            'expectedRefundable' => false,
        ];

        yield 'non-GoPay paid transaction is not refundable' => [
            'paymentType' => PaymentTypeEnum::TYPE_BASIC,
            'externalPaymentStatus' => PaymentStatus::PAID,
            'expectedRefundable' => false,
        ];
    }

    protected function createPayment(string $paymentType): Payment
    {
        $paymentData = new PaymentData();
        $paymentData->type = $paymentType;

        return new Payment($paymentData);
    }

    protected function createPaymentTransactionData(
        ?Payment $payment,
        ?string $externalPaymentStatus,
    ): PaymentTransactionData {
        $paymentTransactionData = new PaymentTransactionDataFactory()->create();
        $paymentTransactionData->payment = $payment;
        $paymentTransactionData->externalPaymentIdentifier = '123456';
        $paymentTransactionData->externalPaymentStatus = $externalPaymentStatus;
        $paymentTransactionData->paidAmount = Money::create('100');
        $paymentTransactionData->refundedAmount = Money::zero();

        return $paymentTransactionData;
    }
}

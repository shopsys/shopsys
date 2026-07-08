<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment\Transaction\Refund;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundFacade;

class PaymentTransactionRefundFacadeTest extends TestCase
{
    public function testManualRefundedAmountCannotBeNegative(): void
    {
        $paymentTransactionRefundFacade = $this->createPaymentTransactionRefundFacade();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Refunded amount should not be negative.');

        $paymentTransactionRefundFacade->changeManualRefundedAmount(
            $this->createPaymentTransactionStub(Money::create('100'), Money::zero()),
            Money::create('-1'),
        );
    }

    public function testManualRefundedAmountCannotBeGreaterThanPaidAmount(): void
    {
        $paymentTransactionRefundFacade = $this->createPaymentTransactionRefundFacade();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Paid amount should be greater than or equal to refunded amount.');

        $paymentTransactionRefundFacade->changeManualRefundedAmount(
            $this->createPaymentTransactionStub(Money::create('100'), Money::zero()),
            Money::create('101'),
        );
    }

    public function testManualRefundedAmountIsChanged(): void
    {
        $paymentTransaction = $this->createPaymentTransactionStub(Money::create('100'), Money::zero());
        $refundedAmount = Money::create('42');
        $paymentTransactionData = (new PaymentTransactionDataFactory())->create();

        $paymentTransactionDataFactory = $this->createMock(PaymentTransactionDataFactory::class);
        $paymentTransactionDataFactory->expects($this->once())
            ->method('createFromPaymentTransaction')
            ->with($paymentTransaction)
            ->willReturn($paymentTransactionData);

        $paymentTransactionFacade = $this->createMock(PaymentTransactionFacade::class);
        $paymentTransactionFacade->expects($this->once())
            ->method('edit')
            ->with(
                123,
                $this->callback(static fn (PaymentTransactionData $paymentTransactionData): bool => $paymentTransactionData->refundedAmount->equals($refundedAmount)),
            );

        $paymentTransactionRefundFacade = $this->createPaymentTransactionRefundFacade(
            $paymentTransactionFacade,
            $paymentTransactionDataFactory,
        );

        $paymentTransactionRefundFacade->changeManualRefundedAmount($paymentTransaction, $refundedAmount);
    }

    public function testOnlineRefundCannotBeExecutedForNonRefundableTransaction(): void
    {
        $paymentTransactionRefundFacade = $this->createPaymentTransactionRefundFacade();

        $paymentTransaction = $this->createPaymentTransactionStub(
            Money::create('100'),
            Money::zero(),
            false,
            hasPayment: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Payment transaction is not refundable.');

        $paymentTransactionRefundFacade->executeOnlineRefund($paymentTransaction, Money::create('10'));
    }

    public function testOnlineRefundAmountCannotBeGreaterThanRefundableAmount(): void
    {
        $paymentTransactionRefundFacade = $this->createPaymentTransactionRefundFacade();

        $paymentTransaction = $this->createPaymentTransactionStub(
            Money::create('100'),
            Money::create('25'),
            true,
            hasPayment: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Refundable amount should be greater than or equal to refunded amount.');

        $paymentTransactionRefundFacade->executeOnlineRefund($paymentTransaction, Money::create('76'));
    }

    public function testOnlineRefundIsExecuted(): void
    {
        $paymentTransaction = $this->createPaymentTransactionStub(
            Money::create('100'),
            Money::create('25'),
            true,
            hasPayment: true,
        );
        $refundAmount = Money::create('50');

        $paymentServiceFacade = $this->createMock(PaymentServiceFacade::class);
        $paymentServiceFacade->expects($this->once())
            ->method('refundTransaction')
            ->with($paymentTransaction, $refundAmount)
            ->willReturn(true);

        $paymentTransactionRefundFacade = $this->createPaymentTransactionRefundFacade(
            paymentServiceFacade: $paymentServiceFacade,
        );

        $this->assertTrue($paymentTransactionRefundFacade->executeOnlineRefund($paymentTransaction, $refundAmount));
    }

    protected function createPaymentTransactionRefundFacade(
        ?PaymentTransactionFacade $paymentTransactionFacade = null,
        ?PaymentTransactionDataFactory $paymentTransactionDataFactory = null,
        ?PaymentServiceFacade $paymentServiceFacade = null,
    ): PaymentTransactionRefundFacade {
        return new PaymentTransactionRefundFacade(
            $paymentTransactionFacade ?? $this->createStub(PaymentTransactionFacade::class),
            $paymentTransactionDataFactory ?? $this->createStub(PaymentTransactionDataFactory::class),
            $paymentServiceFacade ?? $this->createStub(PaymentServiceFacade::class),
        );
    }

    protected function createPaymentTransactionStub(
        Money $paidAmount,
        Money $refundedAmount,
        bool $refundable = false,
        bool $hasPayment = false,
    ): PaymentTransaction {
        $paymentTransaction = $this->createStub(PaymentTransaction::class);
        $paymentTransaction->method('getId')->willReturn(123);
        $paymentTransaction->method('getPaidAmount')->willReturn($paidAmount);
        $paymentTransaction->method('getRefundableAmount')->willReturn($paidAmount->subtract($refundedAmount));
        $paymentTransaction->method('isRefundable')->willReturn($refundable);
        $paymentTransaction->method('getPayment')->willReturn($hasPayment ? $this->createStub(Payment::class) : null);

        return $paymentTransaction;
    }
}

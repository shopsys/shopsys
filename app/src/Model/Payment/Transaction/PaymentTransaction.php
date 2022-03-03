<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction;

use App\Model\Order\Order;
use App\Model\Payment\Payment;
use Doctrine\ORM\Mapping as ORM;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * @ORM\Table(name="payment_transactions")
 * @ORM\Entity
 */
class PaymentTransaction
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \App\Model\Order\Order
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Order", inversedBy="paymentTransactions")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    private Order $order;

    /**
     * @var \App\Model\Payment\Payment|null
     * @ORM\ManyToOne(targetEntity="App\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private ?Payment $payment;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private string $externalPaymentIdentifier;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $externalPaymentStatus;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    private Money $paidAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    private Money $refundedAmount;

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     */
    public function __construct(PaymentTransactionData $paymentTransactionData)
    {
        $this->setData($paymentTransactionData);
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     */
    private function setData(PaymentTransactionData $paymentTransactionData): void
    {
        $this->order = $paymentTransactionData->order;
        $this->payment = $paymentTransactionData->payment;
        $this->paidAmount = $paymentTransactionData->paidAmount;
        $this->externalPaymentIdentifier = $paymentTransactionData->externalPaymentIdentifier;
        $this->externalPaymentStatus = $paymentTransactionData->externalPaymentStatus;
        $this->refundedAmount = $paymentTransactionData->refundedAmount;
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     */
    public function edit(PaymentTransactionData $paymentTransactionData): void
    {
        $this->setData($paymentTransactionData);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Order\Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return \App\Model\Payment\Payment|null
     */
    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    /**
     * @return string
     */
    public function getExternalPaymentIdentifier(): string
    {
        return $this->externalPaymentIdentifier;
    }

    /**
     * @return string|null
     */
    public function getExternalPaymentStatus(): ?string
    {
        return $this->externalPaymentStatus;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPaidAmount(): Money
    {
        return $this->paidAmount;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getRefundedAmount(): Money
    {
        return $this->refundedAmount;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getRefundableAmount(): Money
    {
        return $this->getPaidAmount()->subtract($this->getRefundedAmount());
    }

    /**
     * @return bool
     */
    public function isRefundable(): bool
    {
        return $this->payment->isGoPay() && in_array($this->externalPaymentStatus, [PaymentStatus::PARTIALLY_REFUNDED, PaymentStatus::PAID], true);
    }

    /**
     * @return bool
     */
    public function isPartiallyRefunded(): bool
    {
        return $this->payment->isGoPay() && $this->externalPaymentStatus === PaymentStatus::PARTIALLY_REFUNDED;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        if ($this->payment === null) {
            return false;
        }

        return $this->payment->isGoPay() && $this->externalPaymentStatus === PaymentStatus::PAID;
    }
}

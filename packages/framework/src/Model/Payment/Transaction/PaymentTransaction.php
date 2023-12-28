<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Doctrine\ORM\Mapping as ORM;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\Loggable;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableParentProperty;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Exception\PaymentTransactionHasNoAssignedPayment;

/**
 * @ORM\Table(name="payment_transactions")
 * @ORM\Entity
 */
#[LoggableChild(Loggable::STRATEGY_INCLUDE_ALL)]
class PaymentTransaction
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\Order", inversedBy="paymentTransactions")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
    #[LoggableParentProperty]
    protected $order;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $payment;

    /**
     * @var string
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    protected $externalPaymentIdentifier;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $externalPaymentStatus;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $paidAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $refundedAmount;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     */
    public function __construct(PaymentTransactionData $paymentTransactionData)
    {
        $this->setData($paymentTransactionData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     */
    protected function setData(PaymentTransactionData $paymentTransactionData): void
    {
        $this->order = $paymentTransactionData->order;
        $this->payment = $paymentTransactionData->payment;
        $this->paidAmount = $paymentTransactionData->paidAmount;
        $this->externalPaymentIdentifier = $paymentTransactionData->externalPaymentIdentifier;
        $this->externalPaymentStatus = $paymentTransactionData->externalPaymentStatus;
        $this->refundedAmount = $paymentTransactionData->refundedAmount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData $paymentTransactionData
     */
    public function edit(PaymentTransactionData $paymentTransactionData): void
    {
        $this->setData($paymentTransactionData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getPaymentThrowExceptionIfNull(): Payment
    {
        if ($this->payment === null) {
            throw new PaymentTransactionHasNoAssignedPayment();
        }

        return $this->payment;
    }

    /**
     * @return string
     */
    #[EntityLogIdentify]
    /**
     * @return string
     */
    public function getExternalPaymentIdentifier()
    {
        return $this->externalPaymentIdentifier;
    }

    /**
     * @return string|null
     */
    public function getExternalPaymentStatus()
    {
        return $this->externalPaymentStatus;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getRefundedAmount()
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

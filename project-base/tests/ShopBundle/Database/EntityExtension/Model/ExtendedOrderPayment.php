<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderPaymentData;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

/**
 * @ORM\Entity
 *
 * Base of this class is a copy of OrderPayment entity from FrameworkBundle
 * Reason is described in /project-base/docs/wip_glassbox/entity-extension.md
 *
 * @see \Shopsys\FrameworkBundle\Model\Order\Item\OrderPayment
 */
class ExtendedOrderPayment extends ExtendedOrderItem
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $payment;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $paymentStringField;
    
    public function __construct(
        Order $order,
        string $name,
        Price $price,
        string $vatPercent,
        int $quantity,
        Payment $payment
    ) {
        parent::__construct(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            null,
            null
        );
        $this->payment = $payment;
    }

    public function getPayment(): \Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        return $this->payment;
    }

    public function edit(OrderItemData $orderPaymentData): void
    {
        if ($orderPaymentData instanceof OrderPaymentData) {
            $this->payment = $orderPaymentData->payment;
            parent::edit($orderPaymentData);
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Order\Item\Exception\InvalidArgumentException(
                'Instance of ' . OrderPaymentData::class . ' is required as argument.'
            );
        }
    }

    public function getPaymentStringField(): ?string
    {
        return $this->paymentStringField;
    }

    /**
     * @param string|null $paymentStringField
     */
    public function setPaymentStringField(?string $paymentStringField): void
    {
        $this->paymentStringField = $paymentStringField;
    }
}

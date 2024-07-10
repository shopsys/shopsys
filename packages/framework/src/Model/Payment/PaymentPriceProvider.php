<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PaymentPriceProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     */
    public function __construct(
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderProcessor $orderProcessor,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPaymentPrice(Cart $cart, Payment $payment, DomainConfig $domainConfig): Price
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $domainConfig);
        $orderInput->setPayment($payment);

        $orderData = $this->orderDataFactory->create();

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        return $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PAYMENT];
    }
}

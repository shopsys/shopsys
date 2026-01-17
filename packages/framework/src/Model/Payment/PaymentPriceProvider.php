<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PaymentPriceProvider
{
    public function __construct(
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderProcessor $orderProcessor,
    ) {
    }

    public function getPaymentPrice(Cart $cart, Payment $payment, DomainConfig $domainConfig): PriceInterface
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

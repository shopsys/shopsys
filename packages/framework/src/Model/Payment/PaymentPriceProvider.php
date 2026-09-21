<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PaymentPriceProvider
{
    public function __construct(
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderProcessingFacade $orderProcessingFacade,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
    ) {
    }

    public function getPaymentPrice(Cart $cart, Payment $payment, DomainConfig $domainConfig): PriceInterface
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $domainConfig);
        $orderData = $this->orderProcessingFacade->getProcessedOrderData($orderInput);

        return $this->paymentPriceCalculation->calculatePriceForProcessedOrder($payment, $orderData, $domainConfig->getId());
    }
}

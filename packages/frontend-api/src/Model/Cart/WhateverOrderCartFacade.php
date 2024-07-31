<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;

class WhateverOrderCartFacade
{
    /**
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly Domain $domain,
    ) {
    }

    public function updateCartOrder(Order $order): void
    {
        d('updateCartOrder');
        $orderInput = $this->orderInputFactory->createFromOrder($order, $this->domain->getCurrentDomainConfig());
        // TODO tady bych měl mít data jen taková, která nenastavují middlewary
        $orderData = $this->orderDataFactory->createFromOrder($order);

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );
d('before edit');
d($orderData);
        // TODO do I want to use this method? It can send an email to the customer when the status is changed (might not be a case for updating the cart but still...)
        $this->orderFacade->edit($order->getId(), $orderData); // EDIT bez logiky
    }
}

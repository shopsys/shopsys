<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;

class TransportPriceProvider
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTransportPrice(Cart $cart, Transport $transport, DomainConfig $domainConfig): Price
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $domainConfig);
        $orderInput->setTransport($transport);

        if (!$transport->isPacketery()) {
            $orderInput->cleanAdditionalData(PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER);
        }

        $orderData = $this->orderDataFactory->create();

        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );

        if (count($orderData->getItemsByType(OrderItemTypeEnum::TYPE_TRANSPORT)) === 0) {
            $message = sprintf('Transport price with domain ID "%d", transport ID "%d", and cart total weight %dg not found.', $domainConfig->getId(), $transport->getId(), $cart->getTotalWeight());

            throw new TransportPriceNotFoundException($message);
        }

        return $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_TRANSPORT];
    }
}

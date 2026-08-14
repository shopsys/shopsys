<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;

class TransportPriceProvider
{
    public function __construct(
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderProcessor $orderProcessor,
    ) {
    }

    public function getTransportPrice(Cart $cart, Transport $transport, DomainConfig $domainConfig): PriceInterface
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $domainConfig);

        return $this->resolveTransportPrice($orderInput, $transport, $domainConfig);
    }

    public function getTransportPriceForSingleProduct(
        Product $product,
        Transport $transport,
        DomainConfig $domainConfig,
        ?CustomerUser $customerUser = null,
    ): PriceInterface {
        $orderInput = $this->orderInputFactory->createForSingleProduct($product, $domainConfig, $customerUser);

        return $this->resolveTransportPrice($orderInput, $transport, $domainConfig);
    }

    protected function resolveTransportPrice(
        OrderInput $orderInput,
        Transport $transport,
        DomainConfig $domainConfig,
    ): PriceInterface {
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
            $totalWeight = $orderInput->findAdditionalData(AddTransportMiddleware::ADDITIONAL_DATA_CART_TOTAL_WEIGHT);
            $message = sprintf('Transport price with domain ID "%d", transport ID "%d", and total weight %dg not found.', $domainConfig->getId(), $transport->getId(), $totalWeight);

            throw new TransportPriceNotFoundException($message);
        }

        return $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_TRANSPORT];
    }
}

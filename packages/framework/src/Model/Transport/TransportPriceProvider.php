<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class TransportPriceProvider
{
    public function __construct(
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderProcessingFacade $orderProcessingFacade,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
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
        $orderData = $this->orderProcessingFacade->getProcessedOrderData($orderInput);

        /** @var int $cartTotalWeight */
        $cartTotalWeight = $orderInput->findAdditionalData(AddTransportMiddleware::ADDITIONAL_DATA_CART_TOTAL_WEIGHT) ?? 0;

        return $this->transportPriceCalculation->calculatePriceForProcessedOrder($transport, $orderData, $domainConfig->getId(), $cartTotalWeight);
    }
}

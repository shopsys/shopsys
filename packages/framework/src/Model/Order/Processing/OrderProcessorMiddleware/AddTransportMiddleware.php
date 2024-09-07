<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class AddTransportMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $transport = $orderProcessingData->orderInput->getTransport();

        if ($transport === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $domainId = $orderProcessingData->getDomainId();

        $transportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $orderProcessingData->orderData->getProductsTotalPriceAfterAppliedDiscounts(),
            $domainId,
        );


        $orderData = $orderProcessingData->orderData;

        $orderItemData = $this->getTransportItemData($transportPrice, $transport, $orderProcessingData->getDomainConfig());

        $orderData->addTotalPrice($transportPrice, OrderItemTypeEnum::TYPE_TRANSPORT);

        $orderData->orderTransport = $orderItemData;
        $orderData->transport = $transport;

        $orderData->addItem($orderItemData);

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $transportPrice
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    protected function getTransportItemData(
        Price $transportPrice,
        Transport $transport,
        DomainConfig $domainConfig,
    ): OrderItemData {
        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_TRANSPORT);

        $orderItemData->name = $transport->getName($domainConfig->getLocale());
        $orderItemData->setUnitPrice($transportPrice);
        $orderItemData->setTotalPrice($transportPrice);
        $orderItemData->vatPercent = $transport->getTransportDomain($domainConfig->getId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->transport = $transport;

        return $orderItemData;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class AddTransportMiddleware implements OrderProcessorMiddlewareInterface
{
    public const string ADDITIONAL_DATA_CART_TOTAL_WEIGHT = 'cartTotalWeight';

    public function __construct(
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $transport = $orderProcessingData->orderInput->getTransport();

        if ($transport === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $domainId = $orderProcessingData->getDomainId();

        /** @var int $cartTotalWeight */
        $cartTotalWeight = $orderProcessingData->orderInput->findAdditionalData(static::ADDITIONAL_DATA_CART_TOTAL_WEIGHT) ?? 0;

        try {
            $transportPrice = $this->transportPriceCalculation->calculatePrice(
                $transport,
                $orderProcessingData->orderData->getProductsTotalPriceAfterAppliedDiscounts(),
                $domainId,
                $cartTotalWeight,
                $orderProcessingData->orderData->freeTransportAndPaymentApplied,
            );
        } catch (TransportPriceNotFoundException) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $orderData = $orderProcessingData->orderData;

        $orderItemData = $this->getTransportItemData($transportPrice, $transport, $orderProcessingData->getDomainConfig());

        $orderData->addTotalPrice($transportPrice, OrderItemTypeEnum::TYPE_TRANSPORT);

        $orderData->orderTransport = $orderItemData;

        $orderData->addItem($orderItemData);

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    protected function getTransportItemData(
        PriceInterface $transportPrice,
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

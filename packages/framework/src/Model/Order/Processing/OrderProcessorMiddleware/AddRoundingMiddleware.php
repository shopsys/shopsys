<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;

class AddRoundingMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        $payment = $orderData->orderPayment?->payment;

        if ($payment === null) {
            return $orderProcessingStack->processNext($orderProcessingData);
        }

        $roundingPrice = $this->orderPriceCalculation->calculateOrderRoundingPrice(
            $payment,
            $orderData->currencyRoundingType,
            $orderData->totalPrice,
            $orderProcessingData->getDomainId(),
        );

        if ($roundingPrice !== null && !$roundingPrice->isZero()) {
            $orderData->addItem($this->orderItemDataFactory->createRounding($roundingPrice, $orderProcessingData->getDomainConfig()));
            $orderData->addTotalPrice($roundingPrice, OrderItemTypeEnum::TYPE_ROUNDING);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}

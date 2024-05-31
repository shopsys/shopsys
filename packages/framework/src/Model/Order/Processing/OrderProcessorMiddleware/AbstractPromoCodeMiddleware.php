<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;

abstract class AbstractPromoCodeMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     */
    public function __construct(
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
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
        $appliedPromoCodes = $orderProcessingData->orderInput->getPromoCodes();

        $orderData = $orderProcessingData->orderData;

        foreach ($appliedPromoCodes as $appliedPromoCode) {
            if (!in_array($appliedPromoCode->getDiscountType(), $this->getSupportedTypes(), true)) {
                continue;
            }

            $products = array_map(
                static fn (OrderItemData $orderItemData) => $orderItemData->product,
                $orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT),
            );

            try {
                $validProductIds = $this->currentPromoCodeFacade->validatePromoCode(
                    $appliedPromoCode,
                    $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PRODUCT],
                    $products,
                );

                $promoCodeLimit = $this->promoCodeFacade->getHighestLimitByPromoCodeAndTotalPrice($appliedPromoCode, $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PRODUCT]);
            } catch (PromoCodeException) {
                continue;
            }

            $this->createAndAddOrderItemData(
                $orderData,
                $validProductIds,
                $appliedPromoCode,
                $promoCodeLimit,
                $orderProcessingData,
            );
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param int[] $validProductIds
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $appliedPromoCode
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit $promoCodeLimit
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     */
    abstract protected function createAndAddOrderItemData(
        OrderData $orderData,
        array $validProductIds,
        PromoCode $appliedPromoCode,
        PromoCodeLimit $promoCodeLimit,
        OrderProcessingData $orderProcessingData,
    ): void;

    /**
     * @return int[]
     */
    abstract protected function getSupportedTypes(): array;
}

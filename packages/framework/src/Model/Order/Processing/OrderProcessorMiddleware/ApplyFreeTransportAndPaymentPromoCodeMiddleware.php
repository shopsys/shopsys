<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;

class ApplyFreeTransportAndPaymentPromoCodeMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $appliedPromoCodes = $orderProcessingData->orderInput->getPromoCodes();

        $orderData = $orderProcessingData->orderData;

        foreach ($appliedPromoCodes as $appliedPromoCode) {
            if ($appliedPromoCode->getDiscountType() !== PromoCodeTypeEnum::DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT) {
                continue;
            }

            $products = array_map(
                static fn (OrderItemData $orderItemData) => $orderItemData->product,
                $orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT),
            );

            try {
                $this->currentPromoCodeFacade->validatePromoCode(
                    $appliedPromoCode,
                    $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PRODUCT],
                    $products,
                );
            } catch (PromoCodeException) {
                continue;
            }

            $orderData->promoCode = $appliedPromoCode->getCode();
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }
}

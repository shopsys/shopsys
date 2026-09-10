<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
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
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

abstract class AbstractPromoCodeMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly PromoCodeFacade $promoCodeFacade,
    ) {
    }

    #[Override]
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
            $promoCodeApplicableProductsPrice = $this->getPromoCodeApplicableProductsPrice($orderData);

            try {
                $validProductIds = $this->currentPromoCodeFacade->validatePromoCode(
                    $appliedPromoCode,
                    $promoCodeApplicableProductsPrice,
                    $products,
                );

                $promoCodeLimit = $this->promoCodeFacade->getHighestLimitByPromoCodeAndTotalPrice($appliedPromoCode, $promoCodeApplicableProductsPrice);
            } catch (PromoCodeException) {
                continue;
            }

            $orderData->promoCode = $appliedPromoCode->getCode();

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

    protected function getPromoCodeApplicableProductsPrice(OrderData $orderData): PriceInterface
    {
        return $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_PRODUCT]
            ->subtract($orderData->getGiftVoucherProductItemsTotalPrice());
    }

    /**
     * @param int[] $validProductIds
     */
    abstract protected function createAndAddOrderItemData(
        OrderData $orderData,
        array $validProductIds,
        PromoCode $appliedPromoCode,
        PromoCodeLimit $promoCodeLimit,
        OrderProcessingData $orderProcessingData,
    ): void;

    /**
     * @return string[]
     */
    abstract protected function getSupportedTypes(): array;
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;

class AddProductGiftsMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        foreach ($orderProcessingData->orderInput->getQuantifiedProducts() as $quantifiedProduct) {
            if ($quantifiedProduct->getAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY) !== CartItemTypeEnum::TYPE_PRODUCT_GIFT) {
                continue;
            }

            $this->addProductGiftOrderItemData($orderProcessingData, $quantifiedProduct);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    protected function addProductGiftOrderItemData(
        OrderProcessingData $orderProcessingData,
        QuantifiedProduct $quantifiedProduct,
    ): void {
        $orderData = $orderProcessingData->orderData;

        $quantifiedItemPrice = $this->quantifiedProductPriceCalculation->calculateGiftPrice(
            $quantifiedProduct,
            $orderProcessingData->getDomainId(),
        );

        $orderItemData = $this->orderItemDataFactory->createFromQuantifiedProduct(
            $quantifiedProduct,
            $quantifiedItemPrice,
            $orderProcessingData->getDomainLocale(),
        );
        $orderData->addItem($orderItemData);
        $orderData->addTotalPrice($quantifiedItemPrice->getTotalPrice(), OrderItemTypeEnum::TYPE_PRODUCT_GIFT);
        $orderData->addBasicTotalItemsPrice($quantifiedItemPrice->getTotalPrice());
    }
}

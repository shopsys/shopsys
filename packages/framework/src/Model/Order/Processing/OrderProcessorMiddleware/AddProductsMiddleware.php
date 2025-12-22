<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;

class AddProductsMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        foreach ($orderProcessingData->orderInput->getQuantifiedProducts() as $quantifiedProduct) {
            if ($quantifiedProduct->getAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY) !== CartItemTypeEnum::TYPE_PRODUCT) {
                continue;
            }

            $this->addProductOrderItemData($orderData, $quantifiedProduct, $orderProcessingData);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     */
    protected function addProductOrderItemData(
        OrderData $orderData,
        QuantifiedProduct $quantifiedProduct,
        OrderProcessingData $orderProcessingData,
    ): void {
        $quantifiedPrices = $this->quantifiedProductPriceCalculation->calculateQuantifiedBasicAndSellingPrice(
            $quantifiedProduct,
            $orderProcessingData->getDomainId(),
            $orderProcessingData->orderInput->getCustomerUser(),
        );

        $orderItemData = $this->orderItemDataFactory->createFromQuantifiedProduct(
            $quantifiedProduct,
            $quantifiedPrices->sellingQuantifiedItemPrice,
            $orderProcessingData->getDomainLocale(),
        );

        $orderData->addItem($orderItemData);

        $sellingTotalPrice = $quantifiedPrices->sellingQuantifiedItemPrice->getTotalPrice();
        $basicTotalPrice = $quantifiedPrices->basicQuantifiedItemPrice->getTotalPrice();

        $orderData->addTotalPrice($sellingTotalPrice, OrderItemTypeEnum::TYPE_PRODUCT);
        $orderData->addBasicTotalItemsPrice($basicTotalPrice);

        if ($basicTotalPrice->equals($sellingTotalPrice)) {
            return;
        }

        $priceDifference = $basicTotalPrice->subtract($sellingTotalPrice);

        $orderData->addTotalProductPriceAdjustmentsDiscount($priceDifference);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
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
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        $locale = $orderProcessingData->domainConfig->getLocale();
        $domainId = $orderProcessingData->domainConfig->getId();

        foreach ($orderProcessingData->inputOrderData->getQuantifiedProducts() as $quantifiedProduct) {
            $quantifiedItemPrice = $this->quantifiedProductPriceCalculation->calculatePrice(
                $quantifiedProduct,
                $domainId,
                $orderProcessingData->customerUser,
            );

            $orderItemData = $this->createProductOrderItem($quantifiedItemPrice, $quantifiedProduct, $locale);
            $orderData->addItem($orderItemData);

            $orderData->addTotalPrice($quantifiedItemPrice->getTotalPrice(), OrderItem::TYPE_PRODUCT);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    public function createProductOrderItem(
        QuantifiedItemPrice $quantifiedItemPrice,
        QuantifiedProduct $quantifiedProduct,
        string $locale,
    ): OrderItemData {
        $product = $quantifiedProduct->getProduct();

        $orderItemData = $this->orderItemDataFactory->create(OrderItem::TYPE_PRODUCT);
        $orderItemData->unitPriceWithoutVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithoutVat();
        $orderItemData->unitPriceWithVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithVat();
        $orderItemData->totalPriceWithoutVat = $quantifiedItemPrice->getTotalPrice()->getPriceWithoutVat();
        $orderItemData->totalPriceWithVat = $quantifiedItemPrice->getTotalPrice()->getPriceWithVat();
        $orderItemData->vatPercent = $quantifiedItemPrice->getVat()->getPercent();
        $orderItemData->name = $product->getName($locale);
        $orderItemData->quantity = $quantifiedProduct->getQuantity();
        $orderItemData->unitName = $product->getUnit()->getName($locale);
        $orderItemData->catnum = $product->getCatnum();
        $orderItemData->product = $product;

        return $orderItemData;
    }
}

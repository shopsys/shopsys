<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStackInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;

class AddProductsMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly QuantifiedProductPriceCalculation $quantifiedProductPriceCalculation,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStackInterface $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $locale = $orderProcessingData->domainConfig->getLocale();
        $domainId = $orderProcessingData->domainConfig->getId();

        foreach ($orderProcessingData->cart->getQuantifiedProducts() as $quantifiedProduct) {
            $quantifiedItemPrice = $this->quantifiedProductPriceCalculation->calculatePrice(
                $quantifiedProduct,
                $domainId,
                $customerUser,
            );

            $product = $quantifiedProduct->getProduct();

            $orderItemData = $this->orderItemDataFactory->create();
            $orderItemData->priceWithoutVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithoutVat();
            $orderItemData->priceWithVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithVat();
            $orderItemData->totalPriceWithoutVat = $quantifiedItemPrice->getTotalPrice()->getPriceWithoutVat();
            $orderItemData->totalPriceWithVat = $quantifiedItemPrice->getTotalPrice()->getPriceWithVat();
            $orderItemData->vatPercent = $quantifiedItemPrice->getVat()->getPercent();
            $orderItemData->name = $product->getName($locale);
            $orderItemData->quantity = $quantifiedProduct->getQuantity();
            $orderItemData->unitName = $product->getUnit()->getName($locale);
            $orderItemData->catnum = $product->getCatnum();

            $orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT] = $orderData->totalPriceByItemType[OrderItem::TYPE_PRODUCT]->add($quantifiedItemPrice->getTotalPrice());
            $orderData->totalPrice = $orderData->totalPrice->add($quantifiedItemPrice->getTotalPrice());
            $orderData->itemsWithoutTransportAndPayment[] = $orderItemData;
        }

        return $orderProcessingStack->next()->handle($orderProcessingData, $orderProcessingStack);
    }
}

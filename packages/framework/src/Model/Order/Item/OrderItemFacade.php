<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class OrderItemFacade
{
    protected const int DEFAULT_PRODUCT_QUANTITY = 1;

    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly Domain $domain,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    public function createProductOrderItemData(Order $order, int $productId): OrderItemData
    {
        $product = $this->productRepository->getById($productId);
        $orderDomainConfig = $this->domain->getDomainConfigById($order->getDomainId());

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePricesForCustomerUserAndDomainId(
            $product,
            $order->getDomainId(),
            $order->getCustomerUser(),
        )->sellingProductPrice->getPrice();

        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRODUCT);
        $orderItemData->name = $product->getName($orderDomainConfig->getLocale());
        $orderItemData->setUnitPrice($productPrice);
        $orderItemData->setTotalPrice($productPrice);
        $orderItemData->vatPercent = $product->getVatForDomain($order->getDomainId())->getPercent();
        $orderItemData->quantity = static::DEFAULT_PRODUCT_QUANTITY;
        $orderItemData->unitName = $product->getUnit()->getName($orderDomainConfig->getLocale());
        $orderItemData->catnum = $product->getCatnum();
        $orderItemData->product = $product;

        return $orderItemData;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class OrderItemFacade
{
    protected const int DEFAULT_PRODUCT_QUANTITY = 1;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderRepository $orderRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly Domain $domain,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
        protected readonly OrderItemFactory $orderItemFactory,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param int $orderId
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function addProductToOrder(int $orderId, int $productId): OrderItem
    {
        $order = $this->orderRepository->getById($orderId);
        $product = $this->productRepository->getById($productId);
        $orderDomainConfig = $this->domain->getDomainConfigById($order->getDomainId());

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $order->getDomainId(),
            $order->getCustomerUser(),
        );

        $orderItemData = $this->orderItemDataFactory->create(OrderItem::TYPE_PRODUCT);
        $orderItemData->name = $product->getName($orderDomainConfig->getLocale());
        $orderItemData->unitPriceWithVat = $productPrice->getPriceWithVat();
        $orderItemData->unitPriceWithoutVat = $productPrice->getPriceWithoutVat();
        $orderItemData->vatPercent = $product->getVatForDomain($order->getDomainId())->getPercent();
        $orderItemData->quantity = static::DEFAULT_PRODUCT_QUANTITY;
        $orderItemData->unitName = $product->getUnit()->getName($orderDomainConfig->getLocale());
        $orderItemData->catnum = $product->getCatnum();

        $orderProduct = $this->orderItemFactory->createProduct(
            $orderItemData,
            $order,
            $product,
        );

        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order),
        );

        $this->em->flush();

        return $orderProduct;
    }
}

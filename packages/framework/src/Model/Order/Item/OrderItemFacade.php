<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class OrderItemFacade
{
    protected const DEFAULT_PRODUCT_QUANTITY = 1;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface $orderItemFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderRepository $orderRepository,
        protected readonly ProductRepository $productRepository,
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly Domain $domain,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
        protected readonly OrderItemFactoryInterface $orderItemFactory,
    ) {
    }

    /**
     * @param int $orderId
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem
     */
    public function addProductToOrder($orderId, $productId)
    {
        $order = $this->orderRepository->getById($orderId);
        $product = $this->productRepository->getById($productId);
        $orderDomainConfig = $this->domain->getDomainConfigById($order->getDomainId());

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCustomerUserAndDomainId(
            $product,
            $order->getDomainId(),
            $order->getCustomerUser(),
        );

        $orderProduct = $this->orderItemFactory->createProduct(
            $order,
            $product->getName($orderDomainConfig->getLocale()),
            $productPrice,
            $product->getVatForDomain($order->getDomainId())->getPercent(),
            static::DEFAULT_PRODUCT_QUANTITY,
            $product->getUnit()->getName($orderDomainConfig->getLocale()),
            $product->getCatnum(),
            $product,
        );

        $order->addItem($orderProduct);
        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order),
        );

        $this->em->flush();

        return $orderProduct;
    }
}

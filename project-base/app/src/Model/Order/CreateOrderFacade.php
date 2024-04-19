<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\CreateOrderFacade as BaseCreateOrderFacade;

/**
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method \App\Model\Order\Order createOrder(\App\Model\Order\OrderData $orderData, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method fillOrderItems(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderDiscounts(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderProducts(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderTransport(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderRounding(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method __construct(\Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository, \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository, \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository, \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory, \Doctrine\ORM\EntityManagerInterface $em, \App\Model\Order\Item\OrderItemFactory $orderItemFactory)
 */
class CreateOrderFacade extends BaseCreateOrderFacade
{
}

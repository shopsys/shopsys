<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade as BasePlaceOrderFacade;

/**
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method \App\Model\Order\Order placeOrder(\App\Model\Order\OrderData $orderData, string|null $deliveryAddressUuid = null)
 * @method fillOrderItems(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderDiscounts(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderProducts(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderTransport(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method fillOrderRounding(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository, \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository, \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository, \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory, \Doctrine\ORM\EntityManagerInterface $em, \App\Model\Order\Item\OrderItemFactory $orderItemFactory, \App\Model\Order\Mail\OrderMailFacade $orderMailFacade, \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher $placedOrderMessageDispatcher, \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade)
 * @method \App\Model\Order\Order createOrder(\App\Model\Order\OrderData $orderData)
 */
class PlaceOrderFacade extends BasePlaceOrderFacade
{
}

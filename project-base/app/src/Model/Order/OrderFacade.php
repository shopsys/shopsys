<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderFacade as BaseOrderFacade;

/**
 * @property \App\Model\Order\OrderRepository $orderRepository
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Order\OrderRepository $orderRepository, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade, \App\Model\Order\Mail\OrderMailFacade $orderMailFacade, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \App\Model\Order\Item\OrderItemFactory $orderItemFactory, \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory, \App\Model\Order\OrderDataFactory $orderDataFactory, \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor, \App\Model\Payment\PaymentFacade $paymentFacade, \Shopsys\FrameworkBundle\Model\Order\OrderDeliveryDateFacade $orderDeliveryDateFacade, \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade $withdrawalRequestFacade, \Shopsys\FrameworkBundle\Model\Order\OrderPaidStatusFacade $orderPaidStatusFacade)
 * @method \App\Model\Order\Order edit(int $orderId, \App\Model\Order\OrderData $orderData)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getLastCustomerOrdersByLimit(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer, int $limit, string $locale)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getById(int $orderId)
 * @method array<int, \App\Model\Order\Order> findByIds(int[] $ids)
 * @method \App\Model\Order\Order getByUuid(string $uuid)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method void refreshOrderItemsWithoutTransportAndPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method void calculateOrderItemDataPrices(\App\Model\Order\Item\OrderItemData $orderItemData, int $domainId, string $currencyRoundingType, int $roundingPlaces)
 * @method void updateTransportAndPaymentNamesInOrderData(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method void changeOrderPayment(\App\Model\Order\Order $order, \App\Model\Payment\Payment $payment, bool $updatePaymentPrice = true)
 * @method void updateTrackingNumber(\App\Model\Order\Order $order, string $trackingNumber)
 * @method \App\Model\Order\Order[] getAllWithoutTrackingNumberByTransportType(string $transportType)
 * @method \App\Model\Order\OrderData createOrderDataFromCart(\App\Model\Cart\Cart $cart, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Order\OrderData fillOrderDataFromCart(\App\Model\Order\OrderData $orderData, \App\Model\Cart\Cart $cart, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @method void updatePaymentByLastPaymentTransaction(\App\Model\Order\Order $order)
 * @method void processWithdrawalRequest(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method void recalculateRoundingForOrderData(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order, \App\Model\Payment\Payment $payment, \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $paymentPrice)
 */
class OrderFacade extends BaseOrderFacade
{
}

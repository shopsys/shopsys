<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderFacade as BaseOrderFacade;

/**
 * @property \App\Model\Order\OrderRepository $orderRepository
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository, \App\Model\Order\OrderRepository $orderRepository, \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository, \App\Model\Order\Mail\OrderMailFacade $orderMailFacade, \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade, \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade, \App\Model\Cart\CartFacade $cartFacade, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory, \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \App\Model\Order\Item\OrderItemFactory $orderItemFactory, \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade, \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory, \Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade, \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory, \App\Model\Order\OrderDataFactory $orderDataFactory, \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor, \App\Model\Payment\PaymentFacade $paymentFacade)
 * @method \App\Model\Order\Order edit(int $orderId, \App\Model\Order\OrderData $orderData)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getLastCustomerOrdersByLimit(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer, int $limit, string $locale)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getById(int $orderId)
 * @method array<int,\App\Model\Order\Order> findByIds(int[] $ids)
 * @method \App\Model\Order\Order getByUuid(string $uuid)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method \App\Model\Order\Order getByOrderNumberAndUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method refreshOrderItemsWithoutTransportAndPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method calculateOrderItemDataPrices(\App\Model\Order\Item\OrderItemData $orderItemData, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency)
 * @method updateTransportAndPaymentNamesInOrderData(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method setOrderPaymentStatusPageValidFromNow(\App\Model\Order\Order $order)
 * @method changeOrderPayment(\App\Model\Order\Order $order, \App\Model\Payment\Payment $payment, bool $updatePaymentPrice = true)
 * @method updateTrackingNumber(\App\Model\Order\Order $order, string $trackingNumber)
 * @method \App\Model\Order\Order[] getAllWithoutTrackingNumberByTransportType(string $transportType)
 * @method \App\Model\Order\OrderData createOrderDataFromCart(\App\Model\Cart\Cart $cart, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Order\OrderData fillOrderDataFromCart(\App\Model\Order\OrderData $orderData, \App\Model\Cart\Cart $cart, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @property \App\Model\Payment\PaymentFacade $paymentFacade
 * @method updatePaymentByLastPaymentTransaction(\App\Model\Order\Order $order)
 */
class OrderFacade extends BaseOrderFacade
{
}

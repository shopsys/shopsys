<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade as BaseOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;

/**
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Order\FrontOrderDataMapper $frontOrderDataMapper
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository, \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository, \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository, \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade, \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository, \App\Component\Setting\Setting $setting, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade, \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade, \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory, \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade, \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface $orderFactory, \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation, \App\Model\Order\FrontOrderDataMapper $frontOrderDataMapper, \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension, \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation, \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface $orderItemFactory)
 * @method \App\Model\Order\Order createOrder(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \App\Model\Order\Order createOrderFromFront(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress)
 * @method sendHeurekaOrderInfo(\App\Model\Order\Order $order, bool $disallowHeurekaVerifiedByCustomers)
 * @method \App\Model\Order\Order edit(int $orderId, \App\Model\Order\OrderData $orderData)
 * @method prefillFrontOrderData(\App\Model\Order\FrontOrderData $orderData, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getById(int $orderId)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method \App\Model\Order\Order getByOrderNumberAndUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method setOrderDataAdministrator(\App\Model\Order\OrderData $orderData)
 * @method fillOrderItems(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview)
 * @method fillOrderPayment(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, string $locale)
 * @method fillOrderTransport(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, string $locale)
 * @method fillOrderRounding(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, string $locale)
 * @method addOrderItemDiscount(\App\Model\Order\Item\OrderItem $orderItem, \Shopsys\FrameworkBundle\Model\Pricing\Price $quantifiedItemDiscount, string $locale, float $discountPercent)
 * @method refreshOrderItemsWithoutTransportAndPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method calculateOrderItemDataPrices(\App\Model\Order\Item\OrderItemData $orderItemData, int $domainId)
 * @method updateOrderDataWithDeliveryAddress(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress)
 */
class OrderFacade extends BaseOrderFacade
{
    /**
     * @param \App\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderProducts(Order $order, OrderPreview $orderPreview, string $locale): void
    {
        $quantifiedItemPrices = $orderPreview->getQuantifiedItemsPrices();
        $quantifiedItemDiscounts = $orderPreview->getQuantifiedItemsDiscounts();

        foreach ($orderPreview->getQuantifiedProducts() as $index => $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();

            /* @var $quantifiedItemPrice \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice */
            $quantifiedItemPrice = $quantifiedItemPrices[$index];
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null $quantifiedItemDiscount */
            $quantifiedItemDiscount = $quantifiedItemDiscounts[$index];

            $orderItem = $this->orderItemFactory->createProduct(
                $order,
                $product->getFullname($locale),
                $quantifiedItemPrice->getUnitPrice(),
                $product->getVatForDomain($order->getDomainId())->getPercent(),
                $quantifiedProduct->getQuantity(),
                $product->getUnit()->getName($locale),
                $product->getCatnum(),
                $product
            );

            if ($quantifiedItemDiscount !== null) {
                $this->addOrderItemDiscount($orderItem, $quantifiedItemDiscount, $locale, (float)$orderPreview->getPromoCodeDiscountPercent());
            }
        }
    }
}

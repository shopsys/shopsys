<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Order\Item\OrderItemDataFactory;
use App\Model\Order\Preview\OrderPreview;
use App\Model\Order\Preview\OrderPreviewSplittingFacade;
use App\Model\Order\Preview\SplitOrderPreview;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade as BaseOrderFacade;
use Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\OrderRepository;
use Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview as BaseOrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;

/**
 * @property \App\Component\Setting\Setting $setting
 * @method \App\Model\Order\Order createOrder(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, \App\Model\Customer\User\CustomerUser|null $customerUser)
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
 * @method fillOrderRounding(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, string $locale)
 * @method refreshOrderItemsWithoutTransportAndPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method calculateOrderItemDataPrices(\App\Model\Order\Item\OrderItemData $orderItemData, int $domainId)
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Order\FrontOrderDataMapper $frontOrderDataMapper
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
 * @method updateOrderDataWithDeliveryAddress(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress)
 * @method fillOrderTransport(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, string $locale)
 */
class OrderFacade extends BaseOrderFacade
{
    /**
     * @var \App\Model\Order\Item\OrderItemDataFactory
     */
    private $orderItemDataFactory;

    /**
     * @var \App\Model\Order\Preview\OrderPreviewSplittingFacade
     */
    private $cartSplittingFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface $orderFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \App\Model\Order\FrontOrderDataMapper $frontOrderDataMapper
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \App\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \App\Model\Order\Preview\OrderPreviewSplittingFacade $cartSplittingFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        OrderNumberSequenceRepository $orderNumberSequenceRepository,
        OrderRepository $orderRepository,
        OrderUrlGenerator $orderUrlGenerator,
        OrderStatusRepository $orderStatusRepository,
        OrderMailFacade $orderMailFacade,
        OrderHashGeneratorRepository $orderHashGeneratorRepository,
        Setting $setting,
        Localization $localization,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        CurrentPromoCodeFacade $currentPromoCodeFacade,
        CartFacade $cartFacade,
        CustomerUserFacade $customerUserFacade,
        CurrentCustomerUser $currentCustomerUser,
        OrderPreviewFactory $orderPreviewFactory,
        OrderProductFacade $orderProductFacade,
        HeurekaFacade $heurekaFacade,
        Domain $domain,
        OrderFactoryInterface $orderFactory,
        OrderPriceCalculation $orderPriceCalculation,
        OrderItemPriceCalculation $orderItemPriceCalculation,
        FrontOrderDataMapper $frontOrderDataMapper,
        NumberFormatterExtension $numberFormatterExtension,
        PaymentPriceCalculation $paymentPriceCalculation,
        TransportPriceCalculation $transportPriceCalculation,
        OrderItemFactoryInterface $orderItemFactory,
        OrderItemDataFactory $orderItemDataFactory,
        OrderPreviewSplittingFacade $cartSplittingFacade
    ) {
        parent::__construct(
            $em,
            $orderNumberSequenceRepository,
            $orderRepository,
            $orderUrlGenerator,
            $orderStatusRepository,
            $orderMailFacade,
            $orderHashGeneratorRepository,
            $setting,
            $localization,
            $administratorFrontSecurityFacade,
            $currentPromoCodeFacade,
            $cartFacade,
            $customerUserFacade,
            $currentCustomerUser,
            $orderPreviewFactory,
            $orderProductFacade,
            $heurekaFacade,
            $domain,
            $orderFactory,
            $orderPriceCalculation,
            $orderItemPriceCalculation,
            $frontOrderDataMapper,
            $numberFormatterExtension,
            $paymentPriceCalculation,
            $transportPriceCalculation,
            $orderItemFactory
        );
        $this->orderItemDataFactory = $orderItemDataFactory;
        $this->cartSplittingFacade = $cartSplittingFacade;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \App\Model\Order\Order
     */
    public function createOrderFromFront(OrderData $orderData, ?DeliveryAddress $deliveryAddress)
    {
        $promoCode = $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull();
        if ($promoCode) {
            $promoCode->decreaseRemainingUses();
        }

        $orderData->status = $this->orderStatusRepository->getDefault();
        $splitOrderPreview = $this->cartSplittingFacade->createSplitOrderPreviewForCurrentCustomer($orderData);
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $this->updateOrderDataWithDeliveryAddress($orderData, $deliveryAddress);

        $order = $this->createOrderBySplitOrderPreview($orderData, $splitOrderPreview, $customerUser);
        $this->orderProductFacade->subtractOrderProductsFromStock($order->getProductItems());

        $this->cartFacade->deleteCartOfCurrentCustomerUser();
        $this->currentPromoCodeFacade->removeEnteredPromoCode();

        if ($customerUser instanceof CustomerUser) {
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, $deliveryAddress);
        }

        return $order;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     *
     * @return \App\Model\Order\Order
     */
    public function createOrderBySplitOrderPreview(OrderData $orderData, SplitOrderPreview $splitOrderPreview, ?CustomerUser $customerUser): Order
    {
        $orderNumber = (string)$this->orderNumberSequenceRepository->getNextNumber();
        $orderUrlHash = $this->orderHashGeneratorRepository->getUniqueHash();
        $toFlush = [];

        $this->setOrderDataAdministrator($orderData);

        /** @var \App\Model\Order\Order $order */
        $order = $this->orderFactory->create(
            $orderData,
            $orderNumber,
            $orderUrlHash,
            $customerUser
        );
        $toFlush[] = $order;

        $this->fillOrderItemsBySplitOrderPreview($order, $splitOrderPreview);

        foreach ($order->getItems() as $orderItem) {
            $this->em->persist($orderItem);
            $toFlush[] = $orderItem;
        }

        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order)
        );

        $this->em->persist($order);
        $this->em->flush($toFlush);

        return $order;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     */
    protected function fillOrderItemsBySplitOrderPreview(Order $order, SplitOrderPreview $splitOrderPreview): void
    {
        $locale = $this->domain->getDomainConfigById($order->getDomainId())->getLocale();

        foreach ($splitOrderPreview->getOrderPreviews() as $orderPreview) {
            $this->fillOrderProducts($order, $orderPreview, $locale);
            $this->fillOrderTransportBySplitOrderPreview($order, $orderPreview, $locale, $splitOrderPreview);
        }

        $this->fillOrderPaymentBySplitOrderPreview($order, $splitOrderPreview, $locale);
        $this->fillOrderRoundingBySplitOrderPreview($order, $splitOrderPreview, $locale);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @param string $locale
     */
    private function fillOrderPaymentBySplitOrderPreview(Order $order, SplitOrderPreview $splitOrderPreview, string $locale): void
    {
        $payment = $splitOrderPreview->getPayment();
        $paymentPrice = $splitOrderPreview->getTransportAndPaymentPricesPreview()->getPaymentPrice($payment);

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $payment->getName($locale);
        $orderItemData->priceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->vatPercent = $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->payment = $payment;
        $orderItemData->productType = $splitOrderPreview->getProductTypeForCommonItems();
        $orderPayment = $this->orderItemFactory->createPaymentByOrderItemData(
            $orderItemData,
            $order
        );
        $order->addItem($orderPayment);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @param string $locale
     */
    private function fillOrderRoundingBySplitOrderPreview(Order $order, SplitOrderPreview $splitOrderPreview, string $locale): void
    {
        $roundingPrice = $splitOrderPreview->getRoundingPrice();
        if ($roundingPrice !== null) {
            $orderItemData = $this->orderItemDataFactory->create();
            $orderItemData->name = t('Rounding', [], 'messages', $locale);
            $orderItemData->priceWithoutVat = $roundingPrice->getPriceWithoutVat();
            $orderItemData->priceWithVat = $roundingPrice->getPriceWithVat();
            $orderItemData->vatPercent = '0';
            $orderItemData->quantity = 1;
            $orderItemData->productType = $splitOrderPreview->getProductTypeForCommonItems();

            $this->orderItemFactory->createProductByOrderItemData(
                $orderItemData,
                $order,
                null
            );
        }
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderProducts(BaseOrder $order, BaseOrderPreview $orderPreview, string $locale): void
    {
        $quantifiedItemPrices = $orderPreview->getQuantifiedItemsPrices();
        $quantifiedItemDiscounts = $orderPreview->getQuantifiedItemsDiscounts();

        foreach ($orderPreview->getQuantifiedProducts() as $index => $quantifiedProduct) {
            /** @var \App\Model\Product\Product $product */
            $product = $quantifiedProduct->getProduct();

            /** @var \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice */
            $quantifiedItemPrice = $quantifiedItemPrices[$index];
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null $quantifiedItemDiscount */
            $quantifiedItemDiscount = $quantifiedItemDiscounts[$index];

            $orderItemData = $this->orderItemDataFactory->create();
            $orderItemData->name = $product->getFullname($locale);
            $orderItemData->priceWithoutVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithoutVat();
            $orderItemData->priceWithVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithVat();
            $orderItemData->vatPercent = $product->getVatForDomain($order->getDomainId())->getPercent();
            $orderItemData->quantity = $quantifiedProduct->getQuantity();
            $orderItemData->unitName = $product->getUnit()->getName($locale);
            $orderItemData->catnum = $product->getCatnum();
            $orderItemData->productType = $orderPreview->getProductType();

            $orderItem = $this->orderItemFactory->createProductByOrderItemData(
                $orderItemData,
                $order,
                $product
            );

            if ($quantifiedItemDiscount !== null) {
                $this->addOrderItemDiscount($orderItem, $quantifiedItemDiscount, $locale, (float)$orderPreview->getPromoCodeDiscountPercent());
            }
        }
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     */
    protected function fillOrderTransportBySplitOrderPreview(
        Order $order,
        OrderPreview $orderPreview,
        string $locale,
        SplitOrderPreview $splitOrderPreview
    ): void {
        /** @var \App\Model\Transport\Transport $transport */
        $transport = $orderPreview->getTransport();
        $transportPrice = $splitOrderPreview->getTransportAndPaymentPricesPreview()->getTransportPrice(
            $transport,
            $orderPreview->getProductType()
        );

        $orderItemData = $this->orderItemDataFactory->create();

        $transportName = $transport->getName($locale);
        $stock = $orderPreview->getPersonalPickupStock();
        if ($stock !== null) {
            $transportName = sprintf('%s %s %s %s', $transportName, $stock->getName(), $stock->getStreet(), $stock->getCity());
            $orderItemData->personalPickupStock = $stock;
        }

        $orderItemData->name = $transportName;
        $orderItemData->priceWithoutVat = $transportPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $transportPrice->getPriceWithVat();
        $orderItemData->vatPercent = $transport->getTransportDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->transport = $transport;
        $orderItemData->productType = $orderPreview->getProductType();

        $orderTransport = $this->orderItemFactory->createTransportByOrderItemData(
            $orderItemData,
            $order
        );
        $order->addItem($orderTransport);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderSentPageContent($orderId): string
    {
        $order = $this->getById($orderId);
        $orderDetailUrl = $this->orderUrlGenerator->getOrderDetailUrl($order);
        $orderSentPageContent = $this->setting->getForDomain(Setting::ORDER_SENT_PAGE_CONTENT, $order->getDomainId());

        $transportsInstructions = [];
        foreach ($order->getTransports() as $transport) {
            $transportsInstructions[] = $transport->getInstructions();
        }

        $variables = [
            self::VARIABLE_TRANSPORT_INSTRUCTIONS => implode('<br /> ', $transportsInstructions),
            self::VARIABLE_PAYMENT_INSTRUCTIONS => $order->getPayment()->getInstructions(),
            self::VARIABLE_ORDER_DETAIL_URL => $orderDetailUrl,
            self::VARIABLE_NUMBER => $order->getNumber(),
        ];

        return strtr($orderSentPageContent, $variables);
    }

    /**
     * @param \App\Model\Order\Item\OrderItem $orderItem
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $quantifiedItemDiscount
     * @param string $locale
     * @param float $discountPercent
     */
    protected function addOrderItemDiscount(OrderItem $orderItem, Price $quantifiedItemDiscount, string $locale, float $discountPercent): void
    {
        $name = sprintf(
            '%s %s - %s',
            t('Promo code', [], 'messages', $locale),
            $this->numberFormatterExtension->formatPercent(-$discountPercent, $locale),
            $orderItem->getName()
        );
        $discountPrice = $quantifiedItemDiscount->inverse();

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $name;
        $orderItemData->priceWithoutVat = $discountPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $discountPrice->getPriceWithVat();
        $orderItemData->vatPercent = $orderItem->getVatPercent();
        $orderItemData->quantity = 1;
        $orderItemData->productType = $orderItem->getProductType();

        $this->orderItemFactory->createProductByOrderItemData(
            $orderItemData,
            $orderItem->getOrder(),
            null
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Component\Deprecation\DeprecatedMethodException;
use App\Model\Order\Item\OrderItemDataFactory;
use App\Model\Order\Status\OrderStatus;
use App\Model\Payment\Payment;
use App\Model\Payment\Service\PaymentServiceFacade;
use App\Model\Payment\Transaction\PaymentTransaction;
use App\Model\Payment\Transaction\PaymentTransactionDataFactory;
use App\Model\Payment\Transaction\PaymentTransactionFacade;
use App\Model\Security\LoginAsUserFacade;
use App\Model\Transport\Type\TransportType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser as BaseCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
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
 * @property \App\Model\Order\OrderRepository $orderRepository
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @property \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @property \App\Component\Setting\Setting $setting
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @method sendHeurekaOrderInfo(\App\Model\Order\Order $order, bool $disallowHeurekaVerifiedByCustomers)
 * @method prefillFrontOrderData(\App\Model\Order\FrontOrderData $orderData, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getById(int $orderId)
 * @method \App\Model\Order\Order getByUuid(string $uuid)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuidAndUrlHash(string $uuid, string $urlHash)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method \App\Model\Order\Order getByOrderNumberAndUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method addOrderItemDiscount(\App\Model\Order\Item\OrderItem $orderItem, \Shopsys\FrameworkBundle\Model\Pricing\Price $quantifiedItemDiscount, string $locale, float $discountPercent)
 * @method refreshOrderItemsWithoutTransportAndPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method calculateOrderItemDataPrices(\App\Model\Order\Item\OrderItemData $orderItemData, int $domainId)
 * @method updateOrderDataWithDeliveryAddress(\App\Model\Order\OrderData $orderData, \App\Model\Customer\DeliveryAddress|null $deliveryAddress)
 * @method updateTransportAndPaymentNamesInOrderData(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method fillOrderItems(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class OrderFacade extends BaseOrderFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository
     * @param \App\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactory $orderFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper $frontOrderDataMapper
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \App\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     * @param \App\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     * @param \App\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     * @param \App\Model\Security\LoginAsUserFacade $loginAsUserFacade
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
        private readonly OrderItemDataFactory $orderItemDataFactory,
        private readonly OrderDataFactory $orderDataFactory,
        private readonly PaymentTransactionFacade $paymentTransactionFacade,
        private readonly PaymentServiceFacade $paymentServiceFacade,
        private readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        private readonly LoginAsUserFacade $loginAsUserFacade,
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
            $orderItemFactory,
        );
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated use App\FrontendApi\Model\Order\PlaceOrderFacade::placeOrder() instead
     */
    public function createOrderFromFront(BaseOrderData $orderData, ?DeliveryAddress $deliveryAddress)
    {
        throw new DeprecatedMethodException();
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     */
    protected function setOrderDataAdministrator(OrderData $orderData)
    {
        $currentAdministratorLoggedAsCustomer = $this->loginAsUserFacade->getCurrentAdministratorLoggedAsCustomer();

        if ($currentAdministratorLoggedAsCustomer === null) {
            return;
        }

        $orderData->createdAsAdministrator = $currentAdministratorLoggedAsCustomer;
        $orderData->createdAsAdministratorName = $currentAdministratorLoggedAsCustomer->getRealName();
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @return \App\Model\Order\Order
     */
    public function createOrder(
        BaseOrderData $orderData,
        BaseOrderPreview $orderPreview,
        ?BaseCustomerUser $customerUser = null,
    ): Order {
        $promoCode = $orderPreview->getPromoCode();

        if ($promoCode) {
            $promoCode->decreaseRemainingUses();
        }

        if ($orderData->status === null) {
            /** @var \App\Model\Order\Status\OrderStatus $status */
            $status = $this->orderStatusRepository->getDefault();
            $orderData->status = $status;
        }

        /** @var \App\Model\Order\Order $order */
        $order = parent::createOrder($orderData, $orderPreview, $customerUser);

        return $order;
    }

    /**
     * @param int $orderId
     * @param \App\Model\Order\OrderData $orderData
     * @return \App\Model\Order\Order
     */
    public function edit($orderId, BaseOrderData $orderData)
    {
        $order = $this->orderRepository->getById($orderId);
        $oldOrderStatus = $order->getStatus();

        parent::edit($orderId, $orderData);

        foreach ($orderData->paymentTransactionRefunds as $paymentTransactionId => $paymentTransactionRefundData) {
            $paymentTransaction = $this->paymentTransactionFacade->getById($paymentTransactionId);
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
            $paymentTransactionData->refundedAmount = $paymentTransactionRefundData->refundedAmount;
            $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
        }

        $this->handleRefundTransactions($orderData->paymentTransactionRefunds);

        if ($oldOrderStatus !== $order->getStatus()) {
            $this->orderMailFacade->sendOrderStatusMailByOrder($order);
        }

        return $order;
    }

    /**
     * @param \App\Model\Payment\Transaction\Refund\PaymentTransactionRefundData[] $transactionsIndexedByPaymentTransactionId
     */
    private function handleRefundTransactions(array $transactionsIndexedByPaymentTransactionId): void
    {
        foreach ($transactionsIndexedByPaymentTransactionId as $paymentTransactionId => $paymentTransactionRefundData) {
            if ($paymentTransactionRefundData->executeRefund) {
                $paymentTransaction = $this->paymentTransactionFacade->getById($paymentTransactionId);
                $this->paymentServiceFacade->refundTransaction($paymentTransaction, $paymentTransactionRefundData->refundAmount);
            }
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

            $orderItem = $this->orderItemFactory->createProductByOrderItemData(
                $orderItemData,
                $order,
                $product,
            );

            $this->em->persist($orderItem);
            $this->em->flush();

            if ($quantifiedItemDiscount === null) {
                continue;
            }

            $coupon = $this->addOrderItemDiscountAndReturnIt(
                $orderItem,
                $quantifiedItemDiscount,
                $locale,
                (float)$orderPreview->getPromoCodeDiscountPercent(),
                $orderPreview->getPromoCodeIdentifier(),
            );
            $orderItem->setRelatedOrderItem($coupon);

            $this->em->persist($coupon);
        }
    }

    /**
     * @param \App\Model\Order\Item\OrderItem $orderItem
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $quantifiedItemDiscount
     * @param string $locale
     * @param float $discountPercent
     * @param string|null $promoCodeIdentifier
     * @return \App\Model\Order\Item\OrderItem
     */
    private function addOrderItemDiscountAndReturnIt(
        OrderItem $orderItem,
        Price $quantifiedItemDiscount,
        string $locale,
        float $discountPercent,
        ?string $promoCodeIdentifier = null,
    ): Item\OrderItem {
        $name = sprintf(
            '%s %s - %s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
            $this->numberFormatterExtension->formatPercent(-$discountPercent, $locale),
            $orderItem->getName(),
        );
        $discountPrice = $quantifiedItemDiscount->inverse();

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $name;
        $orderItemData->priceWithoutVat = $discountPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $discountPrice->getPriceWithVat();
        $orderItemData->vatPercent = $orderItem->getVatPercent();
        $orderItemData->quantity = 1;
        $orderItemData->promoCodeIdentifier = $promoCodeIdentifier;
        $orderItemData->relatedOrderItem = $orderItem;

        return $this->orderItemFactory->createProductByOrderItemData(
            $orderItemData,
            $orderItem->getOrder(),
            null,
        );
    }

    /**
     * @param \DateTime $fromDate
     * @return \App\Model\Order\Order[]
     */
    public function getAllUnpaidGoPayOrders(DateTime $fromDate): array
    {
        return $this->orderRepository->getAllUnpaidGoPayOrders($fromDate);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return bool
     */
    public function isUnpaidOrderPaymentChangeable(Order $order): bool
    {
        return $order->getStatus()->getType() === OrderStatus::TYPE_NEW &&
            $order->getPayment()->isGoPay() &&
            count(array_filter($order->getGoPayTransactions(), function (PaymentTransaction $paymentTransaction) {
                return $paymentTransaction->isPaid();
            })) === 0;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Payment\Payment $payment
     * @param int $domainId
     */
    public function changeOrderPayment(Order $order, Payment $payment, int $domainId): void
    {
        $paymentPrice = $this->paymentPriceCalculation->calculateIndependentPrice($payment, $order->getCurrency(), $domainId);

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $payment->getName();
        $orderItemData->priceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->vatPercent = $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->payment = $payment;
        $orderPayment = $this->orderItemFactory->createPaymentByOrderItemData($orderItemData, $order);

        $orderPaymentData = $this->orderItemDataFactory->createFromOrderItem($orderPayment);
        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->orderPayment = $orderPaymentData;
        $order->removeItem($order->getOrderPayment());
        $this->edit($order->getId(), $orderData);
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderFormData
     * @param \App\Model\Payment\Payment[] $payments
     * @param \App\Model\Transport\Transport[] $transports
     * @return \App\Model\Order\FrontOrderData
     */
    public function revalidatePaymentAndTransport(
        FrontOrderData $frontOrderFormData,
        array $payments,
        array $transports,
    ) {
        if ($frontOrderFormData->payment !== null) {
            $isPaymentValid = false;
            $paymentId = $frontOrderFormData->payment->getId();

            foreach ($payments as $payment) {
                if ($payment->getId() === $paymentId) {
                    $isPaymentValid = true;

                    break;
                }
            }

            if ($isPaymentValid === false) {
                $frontOrderFormData->payment = null;
            }
        }

        if ($frontOrderFormData->transport !== null) {
            $transportId = $frontOrderFormData->transport->getId();
            $isTransportValid = false;

            foreach ($transports as $transport) {
                if ($transport->getId() === $transportId) {
                    $isTransportValid = true;

                    break;
                }
            }

            if ($isTransportValid === false) {
                $frontOrderFormData->transport = null;
            }
        }

        return $frontOrderFormData;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderPayment(BaseOrder $order, BaseOrderPreview $orderPreview, string $locale): void
    {
        $payment = $order->getPayment();
        $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $order->getCurrency(),
            $orderPreview->getProductsPrice(),
            $order->getDomainId(),
        );

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $payment->getName($locale);
        $orderItemData->priceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->vatPercent = $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->payment = $payment;
        $orderPayment = $this->orderItemFactory->createPaymentByOrderItemData(
            $orderItemData,
            $order,
        );

        $order->addItem($orderPayment);
        $this->em->persist($orderPayment);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderTransport(BaseOrder $order, BaseOrderPreview $orderPreview, string $locale): void
    {
        $transport = $order->getTransport();
        $transportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $order->getCurrency(),
            $orderPreview->getProductsPrice(),
            $order->getDomainId(),
        );
        $orderItemData = $this->orderItemDataFactory->create();

        $transportName = $transport->getName($locale);
        $pickupStore = $orderPreview->getPersonalPickupStore();

        if ($pickupStore !== null) {
            $transportName = sprintf('%s %s', $transportName, $pickupStore->getName());
            $orderItemData->personalPickupStore = $pickupStore;
        }

        $orderItemData->name = $transportName;
        $orderItemData->priceWithoutVat = $transportPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $transportPrice->getPriceWithVat();
        $orderItemData->vatPercent = $transport->getTransportDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;
        $orderItemData->transport = $transport;

        $orderTransport = $this->orderItemFactory->createTransportByOrderItemData(
            $orderItemData,
            $order,
        );

        $order->addItem($orderTransport);
        $this->em->persist($orderTransport);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderRounding(BaseOrder $order, BaseOrderPreview $orderPreview, string $locale): void
    {
        $roundingPrice = $orderPreview->getRoundingPrice();

        if ($roundingPrice === null) {
            return;
        }

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = t('Rounding', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale);
        $orderItemData->priceWithoutVat = $roundingPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $roundingPrice->getPriceWithVat();
        $orderItemData->vatPercent = '0';
        $orderItemData->quantity = 1;

        $roundingItem = $this->orderItemFactory->createProductByOrderItemData(
            $orderItemData,
            $order,
            null,
        );

        $this->em->persist($roundingItem);
    }

    /**
     * @param \App\Model\Transport\Type\TransportType $transportType
     * @return \App\Model\Order\Order[]
     */
    public function getAllWithoutTrackingNumberByTransportType(TransportType $transportType): array
    {
        return $this->orderRepository->getAllWithoutTrackingNumberByTransportType($transportType);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $trackingNumber
     */
    public function updateTrackingNumber(Order $order, string $trackingNumber): void
    {
        $order->setTrackingNumber($trackingNumber);
        $this->em->flush();
    }
}

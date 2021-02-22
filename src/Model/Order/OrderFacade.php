<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Country\CountryFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use App\Model\Customer\User\RegistrationDataFactory;
use App\Model\Customer\User\RegistrationFacade;
use App\Model\GoPay\GoPayTransaction;
use App\Model\Gtm\GtmHelper;
use App\Model\Order\Item\OrderItemDataFactory;
use App\Model\Order\Status\OrderStatus;
use App\Model\Payment\Payment;
use BadMethodCallException;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
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
 * @property \App\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Order\FrontOrderDataMapper $frontOrderDataMapper
 * @property \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @method \App\Model\Order\Order createOrder(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method sendHeurekaOrderInfo(\App\Model\Order\Order $order, bool $disallowHeurekaVerifiedByCustomers)
 * @method prefillFrontOrderData(\App\Model\Order\FrontOrderData $orderData, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getById(int $orderId)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuidAndUrlHash(string $uuid, string $urlHash)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method \App\Model\Order\Order getByOrderNumberAndUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method setOrderDataAdministrator(\App\Model\Order\OrderData $orderData)
 * @method addOrderItemDiscount(\App\Model\Order\Item\OrderItem $orderItem, \Shopsys\FrameworkBundle\Model\Pricing\Price $quantifiedItemDiscount, string $locale, float $discountPercent)
 * @method refreshOrderItemsWithoutTransportAndPayment(\App\Model\Order\Order $order, \App\Model\Order\OrderData $orderData)
 * @method calculateOrderItemDataPrices(\App\Model\Order\Item\OrderItemData $orderItemData, int $domainId)
 * @method updateOrderDataWithDeliveryAddress(\App\Model\Order\OrderData $orderData, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress)
 * @method updateTransportAndPaymentNamesInOrderData(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method fillOrderItems(\App\Model\Order\Order $order, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview)
 */
class OrderFacade extends BaseOrderFacade
{
    protected const MAX_GENERATE_TRIES = 100;

    /**
     * @var \App\Model\Order\Item\OrderItemDataFactory
     */
    private $orderItemDataFactory;

    /**
     * @var \App\Model\Order\OrderDataFactory
     */
    private $orderDataFactory;

    /**
     * @var \App\Model\Customer\User\RegistrationDataFactory
     */
    private $registrationDataFactory;

    /**
     * @var \App\Model\Customer\User\RegistrationFacade
     */
    private $registrationFacade;

    /**
     * @var \App\Model\Country\CountryFacade
     */
    private $countryFacade;

    /**
     * @var \App\Model\Gtm\GtmHelper
     */
    private $gtmHelper;

    /**
     * @var \App\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private CustomerUserUpdateDataFactory $customerUserUpdateDataFactory;

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
     * @param \App\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Cart\CartFacade $cartFacade
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
     * @param \App\Model\Order\OrderDataFactory $orderDataFactory
     * @param \App\Model\Customer\User\RegistrationDataFactory $registrationDataFactory
     * @param \App\Model\Customer\User\RegistrationFacade $registrationFacade
     * @param \App\Model\Country\CountryFacade $countryFacade
     * @param \App\Model\Gtm\GtmHelper $gtmHelper
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
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
        OrderDataFactory $orderDataFactory,
        RegistrationDataFactory $registrationDataFactory,
        RegistrationFacade $registrationFacade,
        CountryFacade $countryFacade,
        GtmHelper $gtmHelper,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
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
        $this->orderDataFactory = $orderDataFactory;
        $this->registrationDataFactory = $registrationDataFactory;
        $this->registrationFacade = $registrationFacade;
        $this->countryFacade = $countryFacade;
        $this->gtmHelper = $gtmHelper;
        $this->customerUserUpdateDataFactory = $customerUserUpdateDataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createOrderFromFront(BaseOrderData $orderData, ?DeliveryAddress $deliveryAddress)
    {
        throw new BadMethodCallException('Call ' . self::class . '::createOrderFromFrontWithRegistration() instead.');
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @return \App\Model\Order\OrderCreatedResult
     */
    public function createOrderFromFrontWithRegistration(OrderData $orderData, ?DeliveryAddress $deliveryAddress, FrontOrderData $frontOrderData): OrderCreatedResult
    {
        $promoCode = $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull();
        if ($promoCode) {
            $promoCode->decreaseRemainingUses();
        }

        $orderData->isOverLimit = false;
        if ($orderData->transport->isOverLimitTransport() === true) {
            $orderData->isOverLimit = true;
        }

        if ($orderData->isOverLimit === true) {
            /** @var \App\Model\Order\Status\OrderStatus $status */
            $status = $this->orderStatusRepository->findById(OrderStatus::TYPE_OVER_LIMIT);
            $orderData->status = $status;
        } else {
            /** @var \App\Model\Order\Status\OrderStatus $status */
            $status = $this->orderStatusRepository->getDefault();
            $orderData->status = $status;
        }

        $customerUser = $this->findCustomerForOrder($orderData);
        $loginCustomer = false;
        if ($customerUser !== null) {
            $this->updateInactiveCustomerByOrder($frontOrderData, $customerUser);
        } else {
            $customerUser = $this->registerWithOrder($orderData, $frontOrderData);
            $loginCustomer = $customerUser->isActivated();
        }

        $this->updateOrderDataWithDeliveryAddress($orderData, $deliveryAddress);

        $promoCode = $this->currentPromoCodeFacade->getValidEnteredPromoCodeOrNull();
        $this->gtmHelper->amendGtmCouponToOrderData($orderData, $promoCode);

        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($orderData->transport, $orderData->payment);
        $order = $this->createOrder($orderData, $orderPreview, $customerUser);

        $this->cartFacade->deleteCartOfCurrentCustomerUser();
        $this->currentPromoCodeFacade->removeEnteredPromoCode();

        if ($customerUser instanceof CustomerUser) {
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, $deliveryAddress);
        }

        return new OrderCreatedResult($order, $loginCustomer);
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

        if ($oldOrderStatus !== $order->getStatus()) {
            $this->orderMailFacade->sendOrderStatusMailByOrder($order);
        }

        return $order;
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

            /** @var \App\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice */
            $quantifiedItemPrice = $quantifiedItemPrices[$index];
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null $quantifiedItemDiscount */
            $quantifiedItemDiscount = $quantifiedItemDiscounts[$index];

            $orderItemData = $this->orderItemDataFactory->create();
            $orderItemData->name = $product->getFullname($locale);
            if ($quantifiedItemDiscount !== null) {
                $orderItemData->priceWithoutVat = $quantifiedItemPrice->getUnitHighPrice()->getPriceWithoutVat();
                $orderItemData->priceWithVat = $quantifiedItemPrice->getUnitHighPrice()->getPriceWithVat();
            } else {
                $orderItemData->priceWithoutVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithoutVat();
                $orderItemData->priceWithVat = $quantifiedItemPrice->getUnitPrice()->getPriceWithVat();
            }
            $orderItemData->vatPercent = $product->getVatForDomain($order->getDomainId())->getPercent();
            $orderItemData->quantity = $quantifiedProduct->getQuantity();
            $orderItemData->unitName = $product->getUnit()->getName($locale);
            $orderItemData->catnum = $product->getCatnum();

            $orderItem = $this->orderItemFactory->createProductByOrderItemData(
                $orderItemData,
                $order,
                $product
            );

            if ($quantifiedItemDiscount === null) {
                continue;
            }

            $coupon = $this->addOrderItemDiscountAndReturnIt(
                $orderItem,
                $quantifiedItemDiscount,
                $locale,
                (float)$orderPreview->getPromoCodeDiscountPercent(),
                $orderPreview->getPromoCodeIdentifier()
            );
            $orderItem->setRelatedOrderItem($coupon);
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
        ?string $promoCodeIdentifier = null
    ): Item\OrderItem {
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
        $orderItemData->promoCodeIdentifier = $promoCodeIdentifier;
        $orderItemData->relatedOrderItem = $orderItem;

        return $this->orderItemFactory->createProductByOrderItemData(
            $orderItemData,
            $orderItem->getOrder(),
            null
        );
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

        $variables = [
            self::VARIABLE_TRANSPORT_INSTRUCTIONS => $order->getTransport()->getInstructions(),
            self::VARIABLE_PAYMENT_INSTRUCTIONS => $order->getPayment()->getInstructions(),
            self::VARIABLE_ORDER_DETAIL_URL => $orderDetailUrl,
            self::VARIABLE_NUMBER => $order->getNumber(),
        ];

        if ($order->isGoPayPaid()) {
            $variables[self::VARIABLE_PAYMENT_INSTRUCTIONS] = t('You have successfully paid order via GoPay.');
        }

        return strtr($orderSentPageContent, $variables);
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
            count(array_filter($order->getGoPayTransactions(), function (GoPayTransaction $transaction) {
                return $transaction->getGoPayStatus() === PaymentStatus::PAID;
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
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    private function mapFrontOrderDataToCustomerUserUpdateData(
        CustomerUser $customerUser,
        FrontOrderData $frontOrderData
    ): CustomerUserUpdateData {
        $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);

        /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
        $billingAddressData = $customerUserUpdateData->billingAddressData;
        $billingAddressData->companyCustomer = $frontOrderData->companyCustomer;
        if ($frontOrderData->companyCustomer === true) {
            $billingAddressData->companyName = $frontOrderData->companyName;
            $billingAddressData->companyNumber = $frontOrderData->companyNumber;
            $billingAddressData->companyVatNumber = $frontOrderData->companyVatNumber;
            $billingAddressData->companyTaxNumber = $frontOrderData->companyTaxNumber;
        } else {
            $billingAddressData->companyName = null;
            $billingAddressData->companyNumber = null;
            $billingAddressData->companyVatNumber = null;
            $billingAddressData->companyTaxNumber = null;
        }
        $billingAddressData->street = $frontOrderData->street;
        $billingAddressData->city = $frontOrderData->city;
        $billingAddressData->postcode = $frontOrderData->postcode;
        $billingAddressData->activated = true;

        /** @var \App\Model\Customer\User\CustomerUserData $customerUserData */
        $customerUserData = $customerUserUpdateData->customerUserData;
        if ($frontOrderData->companyCustomer === false) {
            $customerUserData->firstName = $frontOrderData->firstName;
            $customerUserData->lastName = $frontOrderData->lastName;
        } else {
            $customerUserData->firstName = null;
            $customerUserData->lastName = null;
        }
        $customerUserData->telephone = $frontOrderData->telephone;

        if ($frontOrderData->deliveryAddressSameAsBillingAddress === false) {
            /** @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData */
            $deliveryAddressData = $customerUserUpdateData->deliveryAddressData;
            $deliveryAddressData->firstName = $frontOrderData->deliveryFirstName;
            $deliveryAddressData->lastName = $frontOrderData->deliveryLastName;
            $deliveryAddressData->telephone = $frontOrderData->deliveryTelephone;
            $deliveryAddressData->companyName = $frontOrderData->deliveryCompanyName;
            $deliveryAddressData->street = $frontOrderData->deliveryStreet;
            $deliveryAddressData->city = $frontOrderData->deliveryCity;
            $deliveryAddressData->postcode = $frontOrderData->deliveryPostcode;
        }

        return $customerUserUpdateData;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @return \App\Model\Customer\User\CustomerUser
     */
    private function registerWithOrder(OrderData $orderData, FrontOrderData $frontOrderData): CustomerUser
    {
        $registrationData = $this->registrationDataFactory->createForDomainId($orderData->domainId);

        if ($orderData->isCompanyCustomer === true) {
            $registrationData->companyName = $orderData->companyName;
            $registrationData->companyNumber = $orderData->companyNumber;
            $registrationData->companyVatNumber = $orderData->companyVatNumber;
            $registrationData->companyTaxNumber = $orderData->companyTaxNumber;
        } else {
            $registrationData->firstName = $orderData->firstName;
            $registrationData->lastName = $orderData->lastName;
        }
        $registrationData->email = $orderData->email;
        $registrationData->telephone = $orderData->telephone;
        $registrationData->street = $orderData->street;
        $registrationData->city = $orderData->city;
        $registrationData->postcode = $orderData->postcode;
        $registrationData->country = $this->countryFacade->getCountryOnCurrentDomain();
        $registrationData->domainId = $orderData->domainId;
        $registrationData->password = $frontOrderData->register ? $orderData->password : ''; // Empty string means non-active user without valid password
        $registrationData->companyCustomer = $orderData->isCompanyCustomer;
        $registrationData->newsletterSubscription = true;
        $registrationData->activated = $frontOrderData->register;

        return $this->registrationFacade->register($registrationData);
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderFormData
     * @param \App\Model\Payment\Payment[] $payments
     * @param \App\Model\Transport\Transport[] $transports
     * @return \App\Model\Order\FrontOrderData
     */
    public function revalidatePaymentAndTransport(FrontOrderData $frontOrderFormData, array $payments, array $transports)
    {
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
     * @param \App\Model\Order\OrderData $orderData
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    private function findCustomerForOrder(OrderData $orderData): ?CustomerUser
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        if ($customerUser === null) {
            /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
            $customerUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain($orderData->email, $orderData->domainId);
        }
        return $customerUser;
    }

    /**
     * @param \App\Model\Order\FrontOrderData $frontOrderData
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    private function updateInactiveCustomerByOrder(FrontOrderData $frontOrderData, CustomerUser $customerUser): void
    {
        if ($frontOrderData->register === true && $customerUser->isActivated() === false) {
            $customerUserUpdateData = $this->mapFrontOrderDataToCustomerUserUpdateData($customerUser, $frontOrderData);
            $this->customerUserFacade->editByCustomerUser($customerUser->getId(), $customerUserUpdateData);
            $this->customerUserFacade->sendActivationMail($customerUser);
        }
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
            $order->getDomainId()
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
            $order
        );

        $order->addItem($orderPayment);
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
            $order->getDomainId()
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

        $orderTransport = $this->orderItemFactory->createTransportByOrderItemData(
            $orderItemData,
            $order
        );

        $order->addItem($orderTransport);
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
        $orderItemData->name = t('Rounding', [], 'messages', $locale);
        $orderItemData->priceWithoutVat = $roundingPrice->getPriceWithoutVat();
        $orderItemData->priceWithVat = $roundingPrice->getPriceWithVat();
        $orderItemData->vatPercent = '0';
        $orderItemData->quantity = 1;

        $this->orderItemFactory->createProductByOrderItemData(
            $orderItemData,
            $order,
            null
        );
    }
}

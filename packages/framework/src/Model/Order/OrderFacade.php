<?php

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Webmozart\Assert\Assert;

class OrderFacade
{
    public const VARIABLE_NUMBER = '{number}';
    public const VARIABLE_ORDER_DETAIL_URL = '{order_detail_url}';
    public const VARIABLE_PAYMENT_INSTRUCTIONS = '{payment_instructions}';
    public const VARIABLE_TRANSPORT_INSTRUCTIONS = '{transport_instructions}';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository
     */
    protected $orderNumberSequenceRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator
     */
    protected $orderUrlGenerator;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository
     */
    protected $orderStatusRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade
     */
    protected $orderMailFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository
     */
    protected $orderHashGeneratorRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade
     */
    protected $administratorFrontSecurityFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     */
    protected $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    protected $cartFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory
     */
    protected $orderPreviewFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade
     */
    protected $orderProductFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade
     */
    protected $heurekaFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface
     */
    protected $orderFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation
     */
    protected $orderPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation
     */
    protected $orderItemPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper
     */
    protected $frontOrderDataMapper;

    /**
     * @var \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension
     */
    protected $numberFormatterExtension;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation
     */
    protected $paymentPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    protected $transportPriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface
     */
    protected $orderItemFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderNumberSequenceRepository $orderNumberSequenceRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderUrlGenerator $orderUrlGenerator
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade $orderMailFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderHashGeneratorRepository $orderHashGeneratorRepository
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface $orderFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\FrontOrderDataMapper $frontOrderDataMapper
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactoryInterface $orderItemFactory
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
        OrderItemFactoryInterface $orderItemFactory
    ) {
        $this->em = $em;
        $this->orderNumberSequenceRepository = $orderNumberSequenceRepository;
        $this->orderRepository = $orderRepository;
        $this->orderStatusRepository = $orderStatusRepository;
        $this->orderMailFacade = $orderMailFacade;
        $this->orderHashGeneratorRepository = $orderHashGeneratorRepository;
        $this->setting = $setting;
        $this->localization = $localization;
        $this->administratorFrontSecurityFacade = $administratorFrontSecurityFacade;
        $this->currentPromoCodeFacade = $currentPromoCodeFacade;
        $this->cartFacade = $cartFacade;
        $this->customerUserFacade = $customerUserFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->orderPreviewFactory = $orderPreviewFactory;
        $this->orderProductFacade = $orderProductFacade;
        $this->heurekaFacade = $heurekaFacade;
        $this->domain = $domain;
        $this->orderFactory = $orderFactory;
        $this->orderPriceCalculation = $orderPriceCalculation;
        $this->orderUrlGenerator = $orderUrlGenerator;
        $this->orderItemPriceCalculation = $orderItemPriceCalculation;
        $this->frontOrderDataMapper = $frontOrderDataMapper;
        $this->numberFormatterExtension = $numberFormatterExtension;
        $this->paymentPriceCalculation = $paymentPriceCalculation;
        $this->transportPriceCalculation = $transportPriceCalculation;
        $this->orderItemFactory = $orderItemFactory;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrder(OrderData $orderData, OrderPreview $orderPreview, ?CustomerUser $customerUser = null)
    {
        $orderNumber = $this->orderNumberSequenceRepository->getNextNumber();
        $orderUrlHash = $this->orderHashGeneratorRepository->getUniqueHash();
        $toFlush = [];

        $this->setOrderDataAdministrator($orderData);

        $order = $this->orderFactory->create(
            $orderData,
            $orderNumber,
            $orderUrlHash,
            $customerUser
        );
        $toFlush[] = $order;

        $this->fillOrderItems($order, $orderPreview);

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
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrderFromFront(OrderData $orderData, ?DeliveryAddress $deliveryAddress)
    {
        $orderData->status = $this->orderStatusRepository->getDefault();
        $orderPreview = $this->orderPreviewFactory->createForCurrentUser($orderData->transport, $orderData->payment);
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $this->updateOrderDataWithDeliveryAddress($orderData, $deliveryAddress);

        $order = $this->createOrder($orderData, $orderPreview, $customerUser);
        $this->orderProductFacade->subtractOrderProductsFromStock($order->getProductItems());

        $this->cartFacade->deleteCartOfCurrentCustomerUser();
        $this->currentPromoCodeFacade->removeEnteredPromoCode();

        if ($customerUser instanceof CustomerUser) {
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, $deliveryAddress);
        }

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param bool $disallowHeurekaVerifiedByCustomers
     */
    public function sendHeurekaOrderInfo(Order $order, $disallowHeurekaVerifiedByCustomers)
    {
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $locale = $domainConfig->getLocale();

        if ($this->heurekaFacade->isHeurekaShopCertificationActivated($order->getDomainId()) &&
            $this->heurekaFacade->isDomainLocaleSupported($locale) &&
            !$disallowHeurekaVerifiedByCustomers
        ) {
            $this->heurekaFacade->sendOrderInfo($order);
        }
    }

    /**
     * @param int $orderId
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function edit($orderId, OrderData $orderData)
    {
        $order = $this->orderRepository->getById($orderId);
        $originalOrderStatus = $order->getStatus();

        $this->calculateOrderItemDataPrices($orderData->orderTransport, $order->getDomainId());
        $this->calculateOrderItemDataPrices($orderData->orderPayment, $order->getDomainId());
        $this->refreshOrderItemsWithoutTransportAndPayment($order, $orderData);
        $this->updateTransportAndPaymentNamesInOrderData($orderData, $order);

        $orderEditResult = $order->edit($orderData);

        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order)
        );

        $this->em->flush();
        if ($orderEditResult->isStatusChanged()) {
            $mailTemplate = $this->orderMailFacade
                ->getMailTemplateByStatusAndDomainId($order->getStatus(), $order->getDomainId());
            if ($mailTemplate->isSendMail()) {
                $this->orderMailFacade->sendEmail($order);
            }
            if ($originalOrderStatus->getType() === OrderStatus::TYPE_CANCELED) {
                $this->orderProductFacade->subtractOrderProductsFromStock($order->getProductItems());
            }
            if ($orderData->status->getType() === OrderStatus::TYPE_CANCELED) {
                $this->orderProductFacade->addOrderProductsToStock($order->getProductItems());
            }
        }

        return $order;
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderSentPageContent($orderId)
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

        return strtr($orderSentPageContent, $variables);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\FrontOrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function prefillFrontOrderData(FrontOrderData $orderData, CustomerUser $customerUser)
    {
        $order = $this->orderRepository->findLastByCustomerUserId($customerUser->getId());
        $this->frontOrderDataMapper->prefillFrontFormData($orderData, $customerUser, $order);
    }

    /**
     * @param int $orderId
     */
    public function deleteById($orderId)
    {
        $order = $this->orderRepository->getById($orderId);
        if ($order->getStatus()->getType() !== OrderStatus::TYPE_CANCELED) {
            $this->orderProductFacade->addOrderProductsToStock($order->getProductItems());
        }
        $order->markAsDeleted();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderList(CustomerUser $customerUser)
    {
        return $this->orderRepository->getCustomerUserOrderList($customerUser);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrderListForEmailByDomainId($email, $domainId)
    {
        return $this->orderRepository->getOrderListForEmailByDomainId($email, $domainId);
    }

    /**
     * @param int $orderId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getById($orderId)
    {
        return $this->orderRepository->getById($orderId);
    }

    /**
     * @param string $urlHash
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUrlHashAndDomain($urlHash, $domainId)
    {
        return $this->orderRepository->getByUrlHashAndDomain($urlHash, $domainId);
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndUser($orderNumber, CustomerUser $customerUser)
    {
        return $this->orderRepository->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData)
    {
        return $this->orderRepository->getOrderListQueryBuilderByQuickSearchData(
            $this->localization->getAdminLocale(),
            $quickSearchData
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function setOrderDataAdministrator(OrderData $orderData)
    {
        if ($this->administratorFrontSecurityFacade->isAdministratorLoggedAsCustomer()) {
            try {
                $currentAdmin = $this->administratorFrontSecurityFacade->getCurrentAdministrator();
                $orderData->createdAsAdministrator = $currentAdmin;
                $orderData->createdAsAdministratorName = $currentAdmin->getRealName();
            } catch (\Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException $ex) {
            }
        }
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function getOrdersCountByEmailAndDomainId($email, $domainId)
    {
        return $this->orderRepository->getOrdersCountByEmailAndDomainId($email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     */
    protected function fillOrderItems(Order $order, OrderPreview $orderPreview)
    {
        $locale = $this->domain->getDomainConfigById($order->getDomainId())->getLocale();

        $this->fillOrderProducts($order, $orderPreview, $locale);
        $this->fillOrderPayment($order, $orderPreview, $locale);
        $this->fillOrderTransport($order, $orderPreview, $locale);
        $this->fillOrderRounding($order, $orderPreview, $locale);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderProducts(Order $order, OrderPreview $orderPreview, string $locale): void
    {
        $quantifiedItemPrices = $orderPreview->getQuantifiedItemsPrices();
        $quantifiedItemDiscounts = $orderPreview->getQuantifiedItemsDiscounts();

        foreach ($orderPreview->getQuantifiedProducts() as $index => $quantifiedProduct) {
            $product = $quantifiedProduct->getProduct();

            /** @var \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice $quantifiedItemPrice */
            $quantifiedItemPrice = $quantifiedItemPrices[$index];
            /** @var \Shopsys\FrameworkBundle\Model\Pricing\Price|null $quantifiedItemDiscount */
            $quantifiedItemDiscount = $quantifiedItemDiscounts[$index];

            $orderItem = $this->orderItemFactory->createProduct(
                $order,
                $product->getName($locale),
                $quantifiedItemPrice->getUnitPrice(),
                $product->getVatForDomain($order->getDomainId())->getPercent(),
                $quantifiedProduct->getQuantity(),
                $product->getUnit()->getName($locale),
                $product->getCatnum(),
                $product
            );

            if ($quantifiedItemDiscount !== null) {
                $this->addOrderItemDiscount($orderItem, $quantifiedItemDiscount, $locale, $orderPreview->getPromoCodeDiscountPercent());
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderPayment(Order $order, OrderPreview $orderPreview, string $locale): void
    {
        $payment = $order->getPayment();
        $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $order->getCurrency(),
            $orderPreview->getProductsPrice(),
            $order->getDomainId()
        );
        $orderPayment = $this->orderItemFactory->createPayment(
            $order,
            $payment->getName($locale),
            $paymentPrice,
            $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent(),
            1,
            $payment
        );
        $order->addItem($orderPayment);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderTransport(Order $order, OrderPreview $orderPreview, string $locale): void
    {
        $transport = $order->getTransport();
        $transportPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $order->getCurrency(),
            $orderPreview->getProductsPrice(),
            $order->getDomainId()
        );
        $orderTransport = $this->orderItemFactory->createTransport(
            $order,
            $transport->getName($locale),
            $transportPrice,
            $transport->getTransportDomain($order->getDomainId())->getVat()->getPercent(),
            1,
            $transport
        );
        $order->addItem($orderTransport);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderRounding(Order $order, OrderPreview $orderPreview, string $locale): void
    {
        if ($orderPreview->getRoundingPrice() !== null) {
            $this->orderItemFactory->createProduct(
                $order,
                t('Rounding', [], 'messages', $locale),
                $orderPreview->getRoundingPrice(),
                0,
                1,
                null,
                null,
                null
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem
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

        $this->orderItemFactory->createProduct(
            $orderItem->getOrder(),
            $name,
            $quantifiedItemDiscount->inverse(),
            $orderItem->getVatPercent(),
            1,
            null,
            null,
            null
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function refreshOrderItemsWithoutTransportAndPayment(Order $order, OrderData $orderData): void
    {
        $orderItemsWithoutTransportAndPaymentData = $orderData->itemsWithoutTransportAndPayment;
        foreach ($order->getItemsWithoutTransportAndPayment() as $orderItem) {
            if (array_key_exists($orderItem->getId(), $orderItemsWithoutTransportAndPaymentData)) {
                $orderItemData = $orderItemsWithoutTransportAndPaymentData[$orderItem->getId()];
                $this->calculateOrderItemDataPrices($orderItemData, $order->getDomainId());
                $orderItem->edit($orderItemData);
            } else {
                $order->removeItem($orderItem);
            }
        }

        foreach ($orderData->getNewItemsWithoutTransportAndPayment() as $newOrderItemData) {
            $this->calculateOrderItemDataPrices($newOrderItemData, $order->getDomainId());

            $newOrderItem = $this->orderItemFactory->createProduct(
                $order,
                $newOrderItemData->name,
                new Price(
                    $newOrderItemData->priceWithoutVat,
                    $newOrderItemData->priceWithVat
                ),
                $newOrderItemData->vatPercent,
                $newOrderItemData->quantity,
                $newOrderItemData->unitName,
                $newOrderItemData->catnum
            );
            if (!$newOrderItemData->usePriceCalculation) {
                $newOrderItem->setTotalPrice(new Price($newOrderItemData->totalPriceWithoutVat, $newOrderItemData->totalPriceWithVat));
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param int $domainId
     */
    protected function calculateOrderItemDataPrices(OrderItemData $orderItemData, int $domainId): void
    {
        if ($orderItemData->usePriceCalculation) {
            $orderItemData->priceWithoutVat = $this->orderItemPriceCalculation->calculatePriceWithoutVat($orderItemData, $domainId);
            $orderItemData->totalPriceWithVat = null;
            $orderItemData->totalPriceWithoutVat = null;
        } else {
            Assert::allNotNull(
                [$orderItemData->priceWithoutVat, $orderItemData->totalPriceWithVat, $orderItemData->totalPriceWithoutVat],
                'When not using price calculation for an order item, all prices must be filled.'
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    protected function updateOrderDataWithDeliveryAddress(OrderData $orderData, ?DeliveryAddress $deliveryAddress)
    {
        if ($deliveryAddress !== null) {
            $orderData->deliveryFirstName = $deliveryAddress->getFirstName();
            $orderData->deliveryLastName = $deliveryAddress->getLastName();
            $orderData->deliveryCompanyName = $deliveryAddress->getCompanyName();
            $orderData->deliveryStreet = $deliveryAddress->getStreet();
            $orderData->deliveryPostcode = $deliveryAddress->getPostcode();
            $orderData->deliveryCity = $deliveryAddress->getCity();
            $orderData->deliveryCountry = $deliveryAddress->getCountry();
            $orderData->deliveryTelephone = $deliveryAddress->getTelephone();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function updateTransportAndPaymentNamesInOrderData(OrderData $orderData, Order $order)
    {
        $orderLocale = $this->domain->getDomainConfigById($order->getDomainId())->getLocale();

        $orderTransportData = $orderData->orderTransport;
        if ($orderTransportData->transport !== $order->getTransport()) {
            $orderTransportData->name = $orderTransportData->transport->getName($orderLocale);
        }

        $orderPaymentData = $orderData->orderPayment;
        if ($orderPaymentData->payment !== $order->getPayment()) {
            $orderPaymentData->name = $orderPaymentData->payment->getName($orderLocale);
        }
    }
}

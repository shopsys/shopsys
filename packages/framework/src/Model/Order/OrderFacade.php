<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Webmozart\Assert\Assert;

class OrderFacade
{
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
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade $heurekaFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFactoryInterface $orderFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation $orderPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation $orderItemPriceCalculation
     * @param \Shopsys\FrameworkBundle\Twig\NumberFormatterExtension $numberFormatterExtension
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation $paymentPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory $orderItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade $paymentServiceFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderNumberSequenceRepository $orderNumberSequenceRepository,
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly OrderHashGeneratorRepository $orderHashGeneratorRepository,
        protected readonly Setting $setting,
        protected readonly Localization $localization,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly CartFacade $cartFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly HeurekaFacade $heurekaFacade,
        protected readonly Domain $domain,
        protected readonly OrderFactoryInterface $orderFactory,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly NumberFormatterExtension $numberFormatterExtension,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
        protected readonly OrderItemFactory $orderItemFactory,
        protected readonly PaymentTransactionFacade $paymentTransactionFacade,
        protected readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly OrderDataFactory $orderDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function createOrder(
        OrderData $orderData,
        OrderPreview $orderPreview,
        ?CustomerUser $customerUser = null,
    ): Order {
        $orderNumber = (string)$this->orderNumberSequenceRepository->getNextNumber();
        $orderUrlHash = $this->orderHashGeneratorRepository->getUniqueHash();

        $this->setOrderDataAdministrator($orderData);

        $order = $this->orderFactory->create(
            $orderData,
            $orderNumber,
            $orderUrlHash,
            $customerUser,
        );

        $this->em->persist($order);

        $this->fillOrderItems($order, $orderPreview);

        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order),
        );

        $this->em->flush();

        return $order;
    }

    /**
     * @param int $orderId
     * @return bool
     */
    public function sendHeurekaOrderInfo(int $orderId): bool
    {
        $order = $this->getById($orderId);
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());
        $locale = $domainConfig->getLocale();

        if ($order->isHeurekaAgreement() === false ||
            $this->heurekaFacade->isDomainLocaleSupported($locale) === false ||
            $this->heurekaFacade->isHeurekaShopCertificationActivated($order->getDomainId()) === false
        ) {
            return false;
        }

        $this->heurekaFacade->sendOrderInfo($order);

        return true;
    }

    /**
     * @param int $orderId
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function edit(int $orderId, OrderData $orderData): Order
    {
        $order = $this->orderRepository->getById($orderId);
        $originalOrderStatus = $order->getStatus();

        $this->calculateOrderItemDataPrices($orderData->orderTransport, $order->getDomainId());
        $this->calculateOrderItemDataPrices($orderData->orderPayment, $order->getDomainId());
        $this->refreshOrderItemsWithoutTransportAndPayment($order, $orderData);
        $this->updateTransportAndPaymentNamesInOrderData($orderData, $order);

        $orderEditResult = $order->edit($orderData);

        $order->setTotalPrice(
            $this->orderPriceCalculation->getOrderTotalPrice($order),
        );

        $this->em->flush();

        if ($orderEditResult->isStatusChanged()) {
            $this->orderMailFacade->sendEmail($order);
        }

        foreach ($orderData->paymentTransactionRefunds as $paymentTransactionId => $paymentTransactionRefundData) {
            $paymentTransaction = $this->paymentTransactionFacade->getById($paymentTransactionId);
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
            $paymentTransactionData->refundedAmount = $paymentTransactionRefundData->refundedAmount;
            $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
        }

        $this->handleRefundTransactions($orderData->paymentTransactionRefunds);

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData[] $transactionsIndexedByPaymentTransactionId
     */
    protected function handleRefundTransactions(array $transactionsIndexedByPaymentTransactionId): void
    {
        foreach ($transactionsIndexedByPaymentTransactionId as $paymentTransactionId => $paymentTransactionRefundData) {
            if ($paymentTransactionRefundData->executeRefund) {
                $paymentTransaction = $this->paymentTransactionFacade->getById($paymentTransactionId);
                $this->paymentServiceFacade->refundTransaction($paymentTransaction, $paymentTransactionRefundData->refundAmount);
            }
        }
    }

    /**
     * @param int $orderId
     */
    public function deleteById(int $orderId): void
    {
        $order = $this->orderRepository->getById($orderId);

        $order->markAsDeleted();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderList(CustomerUser $customerUser): array
    {
        return $this->orderRepository->getCustomerUserOrderList($customerUser);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrderListForEmailByDomainId(string $email, int $domainId): array
    {
        return $this->orderRepository->getOrderListForEmailByDomainId($email, $domainId);
    }

    /**
     * @param int $orderId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getById(int $orderId): Order
    {
        return $this->orderRepository->getById($orderId);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUuid(string $uuid): Order
    {
        return $this->orderRepository->getByUuid($uuid);
    }

    /**
     * @param string $urlHash
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUrlHashAndDomain(string $urlHash, int $domainId): Order
    {
        return $this->orderRepository->getByUrlHashAndDomain($urlHash, $domainId);
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $quickSearchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderListQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): QueryBuilder
    {
        return $this->orderRepository->getOrderListQueryBuilderByQuickSearchData(
            $this->localization->getAdminLocale(),
            $quickSearchData,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function setOrderDataAdministrator(OrderData $orderData): void
    {
        if ($this->administratorFrontSecurityFacade->isAdministratorLoggedAsCustomer()) {
            try {
                $currentAdmin = $this->administratorFrontSecurityFacade->getCurrentAdministrator();
                $orderData->createdAsAdministrator = $currentAdmin;
                $orderData->createdAsAdministratorName = $currentAdmin->getRealName();
            } catch (AdministratorIsNotLoggedException) {
                return;
            }
        }
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return int
     */
    public function getOrdersCountByEmailAndDomainId(string $email, int $domainId): int
    {
        return $this->orderRepository->getOrdersCountByEmailAndDomainId($email, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     */
    protected function fillOrderItems(Order $order, OrderPreview $orderPreview): void
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

            $quantifiedItemPrice = $quantifiedItemPrices[$index];
            $quantifiedItemDiscount = $quantifiedItemDiscounts[$index];

            $orderItemData = $this->orderItemDataFactory->create();
            $orderItemData->name = $product->getName($locale);
            $orderItemData->priceWithVat = $quantifiedItemPrice->getTotalPrice()->getPriceWithVat();
            $orderItemData->priceWithoutVat = $quantifiedItemPrice->getTotalPrice()->getPriceWithoutVat();
            $orderItemData->vatPercent = $product->getVatForDomain($order->getDomainId())->getPercent();
            $orderItemData->quantity = $quantifiedProduct->getQuantity();
            $orderItemData->unitName = $product->getUnit()->getName($locale);
            $orderItemData->catnum = $product->getCatnum();

            $orderItem = $this->orderItemFactory->createProduct(
                $orderItemData,
                $order,
                $product,
            );

            $this->em->persist($orderItem);

            if ($quantifiedItemDiscount !== null) {
                $this->addOrderItemDiscount(
                    $orderItem,
                    $quantifiedItemDiscount,
                    $locale,
                    (float)$orderPreview->getPromoCodeDiscountPercent(),
                );
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
            $order->getDomainId(),
        );

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $payment->getName($locale);
        $orderItemData->priceWithVat = $paymentPrice->getPriceWithVat();
        $orderItemData->priceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderItemData->vatPercent = $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;

        $orderPayment = $this->orderItemFactory->createPayment(
            $orderItemData,
            $order,
            $payment,
        );

        $this->em->persist($orderPayment);
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
            $order->getDomainId(),
        );

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $transport->getName($locale);
        $orderItemData->priceWithVat = $transportPrice->getPriceWithVat();
        $orderItemData->priceWithoutVat = $transportPrice->getPriceWithoutVat();
        $orderItemData->vatPercent = $transport->getTransportDomain($order->getDomainId())->getVat()->getPercent();
        $orderItemData->quantity = 1;

        $orderPayment = $this->orderItemFactory->createTransport(
            $orderItemData,
            $order,
            $transport,
        );

        $this->em->persist($orderPayment);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview $orderPreview
     * @param string $locale
     */
    protected function fillOrderRounding(Order $order, OrderPreview $orderPreview, string $locale): void
    {
        $roundingPrice = $orderPreview->getRoundingPrice();

        if ($roundingPrice === null) {
            return;
        }

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = t('Rounding', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale);
        $orderItemData->priceWithVat = $roundingPrice->getPriceWithVat();
        $orderItemData->priceWithoutVat = $roundingPrice->getPriceWithoutVat();
        $orderItemData->vatPercent = '0';
        $orderItemData->quantity = 1;

        $roundingItem = $this->orderItemFactory->createRounding(
            $orderItemData,
            $order,
        );

        $this->em->persist($roundingItem);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $quantifiedItemDiscount
     * @param string $locale
     * @param float $discountPercent
     */
    protected function addOrderItemDiscount(
        OrderItem $orderItem,
        Price $quantifiedItemDiscount,
        string $locale,
        float $discountPercent,
    ): void {
        $name = sprintf(
            '%s %s - %s',
            t('Promo code', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale),
            $this->numberFormatterExtension->formatPercent(-$discountPercent, $locale),
            $orderItem->getName(),
        );

        $orderItemData = $this->orderItemDataFactory->create();
        $orderItemData->name = $name;
        $orderItemData->priceWithVat = $quantifiedItemDiscount->inverse()->getPriceWithVat();
        $orderItemData->priceWithoutVat = $quantifiedItemDiscount->inverse()->getPriceWithoutVat();
        $orderItemData->vatPercent = $orderItem->getVatPercent();
        $orderItemData->quantity = 1;

        $discountItem = $this->orderItemFactory->createDiscount(
            $orderItemData,
            $orderItem->getOrder(),
        );

        $this->em->persist($discountItem);
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
                $newOrderItemData,
                $order,
                null,
            );

            if ($newOrderItemData->usePriceCalculation) {
                continue;
            }

            $newOrderItem->setTotalPrice(
                new Price($newOrderItemData->totalPriceWithoutVat, $newOrderItemData->totalPriceWithVat),
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param int $domainId
     */
    protected function calculateOrderItemDataPrices(OrderItemData $orderItemData, int $domainId): void
    {
        if ($orderItemData->usePriceCalculation) {
            $orderItemData->priceWithoutVat = $this->orderItemPriceCalculation->calculatePriceWithoutVat(
                $orderItemData,
                $domainId,
            );
            $orderItemData->totalPriceWithVat = null;
            $orderItemData->totalPriceWithoutVat = null;
        } else {
            Assert::allNotNull(
                [$orderItemData->priceWithoutVat, $orderItemData->totalPriceWithVat, $orderItemData->totalPriceWithoutVat],
                'When not using price calculation for an order item, all prices must be filled.',
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    protected function updateOrderDataWithDeliveryAddress(
        OrderData $orderData,
        ?DeliveryAddress $deliveryAddress,
    ): void {
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
    protected function updateTransportAndPaymentNamesInOrderData(OrderData $orderData, Order $order): void
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    public function setOrderPaymentStatusPageValidFromNow(Order $order): void
    {
        $order->setOrderPaymentStatusPageValidFromNow();
        $order->setOrderPaymentStatusPageValidityHashToNull();
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     */
    public function changeOrderPayment(Order $order, Payment $payment): void
    {
        $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
            $payment,
            $order->getCurrency(),
            $order->getTotalProductsPrice(),
            $order->getDomainId(),
        );

        $orderPaymentData = $this->orderItemDataFactory->create();
        $orderPaymentData->name = $payment->getName();
        $orderPaymentData->priceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderPaymentData->priceWithVat = $paymentPrice->getPriceWithVat();
        $orderPaymentData->vatPercent = $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent();
        $orderPaymentData->quantity = 1;
        $orderPaymentData->payment = $payment;

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->orderPayment = $orderPaymentData;
        $this->edit($order->getId(), $orderData);
    }
}

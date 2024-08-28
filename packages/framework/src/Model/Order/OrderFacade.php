<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Heureka\HeurekaFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Mail\OrderMailFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportType;
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

        $this->calculateOrderItemDataPrices($orderData->orderTransport, $order->getDomainId());
        $this->calculateOrderItemDataPrices($orderData->orderPayment, $order->getDomainId());
        $this->refreshOrderItemsWithoutTransportAndPayment($order, $orderData);
        $this->updateTransportAndPaymentNamesInOrderData($orderData, $order);

        $orderEditResult = $order->edit($orderData);

        $orderTotalPrice = $this->orderPriceCalculation->getOrderTotalPrice($order);
        $order->setTotalPrices($orderTotalPrice->getPrice(), $orderTotalPrice->getProductPrice());

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
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getLastCustomerOrdersByLimit(Customer $customer, int $limit, string $locale): array
    {
        return $this->orderRepository->getLastCustomerOrdersByLimit($customer, $limit, $locale);
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     */
    protected function refreshOrderItemsWithoutTransportAndPayment(Order $order, OrderData $orderData): void
    {
        $orderItemsWithoutTransportAndPaymentData = $orderData->getItemsWithoutTransportAndPayment();

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
            $orderItemData->unitPriceWithoutVat = $this->orderItemPriceCalculation->calculatePriceWithoutVat(
                $orderItemData,
                $domainId,
            );
            $orderItemData->totalPriceWithVat = null;
            $orderItemData->totalPriceWithoutVat = null;
        } else {
            Assert::allNotNull(
                [$orderItemData->unitPriceWithoutVat, $orderItemData->totalPriceWithVat, $orderItemData->totalPriceWithoutVat],
                'When not using price calculation for an order item, all prices must be filled.',
            );
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

        $orderPaymentData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PAYMENT);
        $orderPaymentData->name = $payment->getName();
        $orderPaymentData->unitPriceWithoutVat = $paymentPrice->getPriceWithoutVat();
        $orderPaymentData->unitPriceWithVat = $paymentPrice->getPriceWithVat();
        $orderPaymentData->vatPercent = $payment->getPaymentDomain($order->getDomainId())->getVat()->getPercent();
        $orderPaymentData->quantity = 1;
        $orderPaymentData->payment = $payment;

        $orderData = $this->orderDataFactory->createFromOrder($order);
        $orderData->orderPayment = $orderPaymentData;
        $this->edit($order->getId(), $orderData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $trackingNumber
     */
    public function updateTrackingNumber(Order $order, string $trackingNumber): void
    {
        $order->setTrackingNumber($trackingNumber);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportType $transportType
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllWithoutTrackingNumberByTransportType(TransportType $transportType): array
    {
        return $this->orderRepository->getAllWithoutTrackingNumberByTransportType($transportType);
    }
}

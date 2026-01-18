<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
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
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Webmozart\Assert\Assert;

class OrderFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderNumberSequenceRepository $orderNumberSequenceRepository,
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderUrlGenerator $orderUrlGenerator,
        protected readonly OrderStatusFacade $orderStatusFacade,
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
        protected readonly OrderFactory $orderFactory,
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
        protected readonly PricingSetting $pricingSetting,
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly PaymentFacade $paymentFacade,
        protected readonly OrderDeliveryDateFacade $orderDeliveryDateFacade,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
    ) {
    }

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

    public function edit(int $orderId, OrderData $orderData): Order
    {
        $order = $this->orderRepository->getById($orderId);

        $this->calculateOrderItemDataPrices($orderData->orderTransport, $order->getDomainId(), $orderData->currency);
        $this->calculateOrderItemDataPrices($orderData->orderPayment, $order->getDomainId(), $orderData->currency);
        $this->refreshOrderItemsWithoutTransportAndPayment($order, $orderData);
        $this->updateTransportAndPaymentNamesInOrderData($orderData, $order);

        $orderEditResult = $order->edit($orderData);

        $orderTotalPrice = $this->orderPriceCalculation->getOrderTotalPrice($order);
        $order->setTotalPrices($orderTotalPrice->getPrice(), $orderTotalPrice->getProductPrice());

        $this->em->flush();

        if ($orderEditResult->isStatusChanged()) {
            $this->orderMailFacade->sendEmail($order, $order->getStatus());
            $this->orderDeliveryDateFacade->setDeliveredNowIfNecessary($order);
        }

        foreach ($orderData->paymentTransactionRefunds as $paymentTransactionId => $paymentTransactionRefundData) {
            $paymentTransaction = $this->paymentTransactionFacade->getById($paymentTransactionId);
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
            $paymentTransactionData->refundedAmount = $paymentTransactionRefundData->refundedAmount;
            $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
        }

        $this->handleRefundTransactions($orderData->paymentTransactionRefunds);

        $this->processWithdrawalRequest($order, $orderData);

        return $order;
    }

    protected function processWithdrawalRequest(Order $order, OrderData $orderData): void
    {
        if ($orderData->withdrawalRequestData === null) {
            return;
        }

        $existingWithdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

        if ($existingWithdrawalRequest !== null) {
            $this->withdrawalRequestFacade->edit(
                $existingWithdrawalRequest->getId(),
                $orderData->withdrawalRequestData,
            );
        } elseif ($orderData->status === $this->orderStatusFacade->getByType(OrderStatusTypeEnum::TYPE_WITHDRAWN)) {
            $this->withdrawalRequestFacade->createOnly($order, $orderData->withdrawalRequestData);
        }
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData> $transactionsIndexedByPaymentTransactionId
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

    public function deleteById(int $orderId): void
    {
        $order = $this->orderRepository->getById($orderId);

        $order->markAsDeleted();
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderList(CustomerUser $customerUser): array
    {
        return $this->orderRepository->getCustomerUserOrderList($customerUser);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getLastCustomerOrdersByLimit(Customer $customer, int $limit, string $locale): array
    {
        return $this->orderRepository->getLastCustomerOrdersByLimit($customer, $limit, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getOrderListForEmailByDomainId(string $email, int $domainId): array
    {
        return $this->orderRepository->getOrderListForEmailByDomainId($email, $domainId);
    }

    public function getById(int $orderId): Order
    {
        return $this->orderRepository->getById($orderId);
    }

    /**
     * @param array<int, int> $ids
     * @return array<int, \Shopsys\FrameworkBundle\Model\Order\Order>
     */
    public function findByIds(array $ids): array
    {
        return $this->orderRepository->findByIds($ids);
    }

    public function getByUuid(string $uuid): Order
    {
        return $this->orderRepository->getByUuid($uuid);
    }

    public function getByUrlHashAndDomain(string $urlHash, int $domainId): Order
    {
        return $this->orderRepository->getByUrlHashAndDomain($urlHash, $domainId);
    }

    public function getByOrderNumberAndUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }

    public function getOrderListQueryBuilderByQuickSearchData(QuickSearchFormData $quickSearchData): QueryBuilder
    {
        return $this->orderRepository->getOrderListQueryBuilderByQuickSearchData(
            $this->localization->getCurrentLocaleForTranslatableEntities(),
            $quickSearchData,
        );
    }

    protected function refreshOrderItemsWithoutTransportAndPayment(Order $order, OrderData $orderData): void
    {
        $orderItemsWithoutTransportAndPaymentData = $orderData->getItemsWithoutTransportAndPayment();

        foreach ($order->getItemsWithoutTransportAndPayment() as $orderItem) {
            if (array_key_exists($orderItem->getId(), $orderItemsWithoutTransportAndPaymentData)) {
                $orderItemData = $orderItemsWithoutTransportAndPaymentData[$orderItem->getId()];
                $this->calculateOrderItemDataPrices($orderItemData, $order->getDomainId(), $orderData->currency);
                $orderItem->edit($orderItemData);
            } else {
                $order->removeItem($orderItem);
            }
        }

        foreach ($orderData->getNewItemsWithoutTransportAndPayment() as $newOrderItemData) {
            $this->calculateOrderItemDataPrices($newOrderItemData, $order->getDomainId(), $orderData->currency);

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

    protected function calculateOrderItemDataPrices(
        OrderItemData $orderItemData,
        int $domainId,
        Currency $currency,
    ): void {
        if ($orderItemData->usePriceCalculation) {
            switch ($this->pricingSetting->getInputPriceType()) {
                case PricingSetting::PRICE_TYPE_WITH_VAT:
                    $orderItemData->unitPriceWithoutVat = $this->orderItemPriceCalculation->calculatePriceWithoutVatForInputPriceWithVat(
                        $orderItemData,
                        $domainId,
                    );

                    break;
                case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                    $orderItemData->unitPriceWithVat = $this->orderItemPriceCalculation->calculatePriceWithVatForInputPriceWithoutVat(
                        $orderItemData,
                        $domainId,
                        $currency,
                    );

                    break;
                default:
                    throw new InvalidInputPriceTypeException();
            }

            $orderItemData->totalPriceWithVat = null;
            $orderItemData->totalPriceWithoutVat = null;
        } else {
            Assert::allNotNull(
                [$orderItemData->unitPriceWithVat, $orderItemData->unitPriceWithoutVat, $orderItemData->totalPriceWithVat, $orderItemData->totalPriceWithoutVat],
                'When not using price calculation for an order item, all prices must be filled.',
            );
        }
    }

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

    public function setOrderPaymentStatusPageValidFromNow(Order $order): void
    {
        $order->setOrderPaymentStatusPageValidFromNow();
        $order->setOrderPaymentStatusPageValidityHashToNull();
        $this->em->flush();
    }

    public function changeOrderPayment(Order $order, Payment $payment, bool $updatePaymentPrice = true): void
    {
        $previousPaymentItems = $order->getItemsByType(OrderItemTypeEnum::TYPE_PAYMENT);

        if ($updatePaymentPrice || count($previousPaymentItems) === 0) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $order->getCurrency(),
                $order->getTotalProductsPrice(),
                $order->getDomainId(),
                $order->isFreeTransportAndPaymentApplied(),
            );
        } else {
            $paymentPrice = reset($previousPaymentItems)->getPrice();
        }

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

    public function updateTrackingNumber(Order $order, string $trackingNumber): void
    {
        $order->setTrackingNumber($trackingNumber);
        $this->em->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getAllWithoutTrackingNumberByTransportType(string $transportType): array
    {
        return $this->orderRepository->getAllWithoutTrackingNumberByTransportType($transportType);
    }

    public function createOrderDataFromCart(Cart $cart, DomainConfig $domainConfig): OrderData
    {
        $orderData = $this->orderDataFactory->create();

        return $this->fillOrderDataFromCart($orderData, $cart, $domainConfig);
    }

    protected function fillOrderDataFromCart(OrderData $orderData, Cart $cart, DomainConfig $domainConfig): OrderData
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $domainConfig);

        return $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );
    }

    public function updatePaymentByLastPaymentTransaction(Order $order): void
    {
        $lastPaymentTransaction = $order->getLastTransaction();

        if ($lastPaymentTransaction === null) {
            return;
        }

        $paymentMethod = $lastPaymentTransaction->getExternalPaymentMethod();

        if ($paymentMethod === null) {
            return;
        }

        $payment = $this->paymentFacade->findPaymentByExternalMethodTransportAndDomainId(
            $paymentMethod,
            $order->getTransport(),
            $order->getDomainId(),
        );

        if ($payment === null || $payment === $order->getPayment()) {
            return;
        }

        $this->changeOrderPayment($order, $payment, false);

        $lastPaymentTransaction->setPayment($payment);

        $this->em->flush();
    }
}

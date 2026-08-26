<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
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
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Webmozart\Assert\Assert;

class OrderFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderStatusFacade $orderStatusFacade,
        protected readonly OrderMailFacade $orderMailFacade,
        protected readonly Localization $localization,
        protected readonly HeurekaFacade $heurekaFacade,
        protected readonly Domain $domain,
        protected readonly OrderPriceCalculation $orderPriceCalculation,
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly PaymentPriceCalculation $paymentPriceCalculation,
        protected readonly OrderItemFactory $orderItemFactory,
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

        $this->calculateOrderItemDataPrices($orderData->orderTransport, $order->getDomainId(), $order->getCurrencyRoundingType(), $order->getCurrencyRoundingPlacesPriceWithoutVat());
        $this->calculateOrderItemDataPrices($orderData->orderPayment, $order->getDomainId(), $order->getCurrencyRoundingType(), $order->getCurrencyRoundingPlacesPriceWithoutVat());
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
     * @param int[] $ids
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
                $this->calculateOrderItemDataPrices($orderItemData, $order->getDomainId(), $order->getCurrencyRoundingType(), $order->getCurrencyRoundingPlacesPriceWithoutVat());
                $orderItem->edit($orderItemData);
            } else {
                $order->removeItem($orderItem);
            }
        }

        foreach ($orderData->getNewItemsWithoutTransportAndPayment() as $newOrderItemData) {
            $this->calculateOrderItemDataPrices($newOrderItemData, $order->getDomainId(), $order->getCurrencyRoundingType(), $order->getCurrencyRoundingPlacesPriceWithoutVat());

            $newOrderItem = match ($newOrderItemData->type) {
                OrderItemTypeEnum::TYPE_ROUNDING => $this->orderItemFactory->createRounding($newOrderItemData, $order),
                OrderItemTypeEnum::TYPE_DISCOUNT => $this->orderItemFactory->createDiscount($newOrderItemData, $order),
                default => $this->orderItemFactory->createProduct($newOrderItemData, $order, $newOrderItemData->product),
            };

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
        string $currencyRoundingType,
        int $roundingPlaces,
    ): void {
        if ($orderItemData->usePriceCalculation) {
            switch ($this->pricingSetting->getInputPriceType()) {
                case PricingSetting::PRICE_TYPE_WITH_VAT:
                    $orderItemData->unitPriceWithoutVat = $this->orderItemPriceCalculation->calculatePriceWithoutVatForInputPriceWithVat(
                        $orderItemData,
                        $domainId,
                        $roundingPlaces,
                    );

                    break;
                case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                    $orderItemData->unitPriceWithVat = $this->orderItemPriceCalculation->calculatePriceWithVatForInputPriceWithoutVat(
                        $orderItemData,
                        $domainId,
                        $currencyRoundingType,
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

    public function changeOrderPayment(Order $order, Payment $payment, bool $updatePaymentPrice = true): void
    {
        $previousPaymentItems = $order->getItemsByType(OrderItemTypeEnum::TYPE_PAYMENT);

        if ($updatePaymentPrice || count($previousPaymentItems) === 0) {
            $paymentPrice = $this->paymentPriceCalculation->calculatePrice(
                $payment,
                $order->getTotalProductsPrice(),
                $order->getDomainId(),
                $order->isFreeTransportAndPaymentApplied(),
                $order->getCurrencyRoundingType(),
                $order->getCurrencyRoundingPlacesPriceWithoutVat(),
            );
        } else {
            $paymentPrice = array_first($previousPaymentItems)->getPrice();
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

        $this->recalculateRoundingForOrderData($orderData, $order, $payment, $paymentPrice);

        $this->edit($order->getId(), $orderData);
    }

    protected function recalculateRoundingForOrderData(
        OrderData $orderData,
        Order $order,
        Payment $payment,
        PriceInterface $paymentPrice,
    ): void {
        $totalExcludingRoundingAndPayment = $order->getTotalPriceExcludingItemTypes([OrderItemTypeEnum::TYPE_ROUNDING, OrderItemTypeEnum::TYPE_PAYMENT]);
        $totalForRounding = new Price(
            $totalExcludingRoundingAndPayment->getPriceWithoutVat()->add($paymentPrice->getPriceWithoutVat()),
            $totalExcludingRoundingAndPayment->getPriceWithVat()->add($paymentPrice->getPriceWithVat()),
        );

        $roundingPrice = $this->orderPriceCalculation->calculateOrderRoundingPrice(
            $payment,
            $order->getCurrencyRoundingType(),
            $totalForRounding,
            $order->getDomainId(),
        );

        $existingRoundingItem = array_first($orderData->getItemsByType(OrderItemTypeEnum::TYPE_ROUNDING));
        $needsRounding = $roundingPrice !== null && !$roundingPrice->isZero();

        if ($existingRoundingItem !== null && $needsRounding) {
            $existingRoundingItem->setUnitPrice($roundingPrice);
            $existingRoundingItem->setTotalPrice($roundingPrice);

            return;
        }

        if ($existingRoundingItem !== null) {
            $orderData->items = array_filter(
                $orderData->items,
                static fn (OrderItemData $item) => $item->type !== OrderItemTypeEnum::TYPE_ROUNDING,
            );

            return;
        }

        if (!$needsRounding) {
            return;
        }

        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());

        $orderData->items[OrderData::NEW_ITEM_PREFIX . 'rounding'] = $this->orderItemDataFactory->createRounding($roundingPrice, $domainConfig);
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

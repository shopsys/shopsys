<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderDataFactory
{
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly OrderItemTypeEnum $orderItemTypeEnum,
        protected readonly WithdrawalRequestDataFactory $withdrawalRequestDataFactory,
        protected readonly WithdrawalRequestFacade $withdrawalRequestFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function createInstance(): OrderData
    {
        return new OrderData();
    }

    public function create(): OrderData
    {
        $orderData = $this->createInstance();
        $orderData->createdAt = $this->clock->now();

        return $this->fillZeroPrices($orderData);
    }

    public function createFromOrder(Order $order): OrderData
    {
        $orderData = $this->create();

        $this->fillFromOrder($orderData, $order);

        return $orderData;
    }

    protected function fillFromOrder(OrderData $orderData, Order $order): void
    {
        $orderData->orderNumber = $order->getNumber();
        $orderData->status = $order->getStatus();
        $orderData->firstName = $order->getFirstName();
        $orderData->lastName = $order->getLastName();
        $orderData->email = $order->getEmail();
        $orderData->telephone = $order->getTelephoneData();
        $orderData->companyName = $order->getCompanyName();
        $orderData->companyNumber = $order->getCompanyNumber();
        $orderData->companyTaxNumber = $order->getCompanyTaxNumber();
        $orderData->isCompanyCustomer = $order->isCompanyCustomer();
        $orderData->street = $order->getStreet();
        $orderData->city = $order->getCity();
        $orderData->postcode = $order->getPostcode();
        $orderData->country = $order->getCountry();
        $this->fillDeliveryAddressFromOrder($orderData, $order);
        $orderData->note = $order->getNote();

        $this->fillItemsFromOrder($orderData, $order);

        $orderData->createdAt = $order->getCreatedAt();
        $orderData->deliveredAt = $order->getDeliveredAt();

        $orderData->domainId = $order->getDomainId();
        $orderData->currencyCode = $order->getCurrencyCode();
        $orderData->currencyRoundingType = $order->getCurrencyRoundingType();
        $orderData->currencyRoundingPlacesPriceWithoutVat = $order->getCurrencyRoundingPlacesPriceWithoutVat();
        $orderData->currencyMinFractionDigits = $order->getCurrencyMinFractionDigits();
        $orderData->createdAsAdministrator = $order->getCreatedAsAdministrator();
        $orderData->createdAsAdministratorName = $order->getCreatedAsAdministratorName();
        $orderData->orderTransport = $this->orderItemDataFactory->createFromOrderItem($order->getTransportItem());
        $orderData->orderPayment = $this->orderItemDataFactory->createFromOrderItem($order->getPaymentItem());

        $orderData->goPayBankSwift = $order->getGoPayBankSwift();

        $orderData->heurekaAgreement = $order->isHeurekaAgreement();
        $orderData->trackingNumber = $order->getTrackingNumber();
        $orderData->promoCode = $order->getPromoCode();
        $orderData->freeTransportAndPaymentApplied = $order->isFreeTransportAndPaymentApplied();

        $withdrawalRequest = $this->withdrawalRequestFacade->findByOrder($order);

        if ($withdrawalRequest !== null) {
            $orderData->withdrawalRequestData = $this->withdrawalRequestDataFactory->createFromWithdrawalRequest($withdrawalRequest);
        }
    }

    protected function fillDeliveryAddressFromOrder(OrderData $orderData, Order $order): void
    {
        $orderData->deliveryAddressSameAsBillingAddress = $order->isDeliveryAddressSameAsBillingAddress();

        if ($order->isDeliveryAddressSameAsBillingAddress()) {
            return;
        }

        $orderData->deliveryFirstName = $order->getDeliveryFirstName();
        $orderData->deliveryLastName = $order->getDeliveryLastName();
        $orderData->deliveryCompanyName = $order->getDeliveryCompanyName();
        $orderData->deliveryTelephone = $order->getDeliveryTelephoneData();
        $orderData->deliveryStreet = $order->getDeliveryStreet();
        $orderData->deliveryCity = $order->getDeliveryCity();
        $orderData->deliveryPostcode = $order->getDeliveryPostcode();
        $orderData->deliveryCountry = $order->getDeliveryCountry();
    }

    protected function fillItemsFromOrder(OrderData $orderData, Order $order): void
    {
        foreach ($order->getItemsSortedWithRelatedItems() as $orderItem) {
            if (array_key_exists($orderItem->getId(), $orderData->items)) {
                continue;
            }

            $orderItemData = $this->orderItemDataFactory->createFromOrderItem($orderItem);

            foreach ($orderItem->getRelatedItems() as $relatedItem) {
                $relatedOrderItemData = $this->orderItemDataFactory->createFromOrderItem($relatedItem);
                $orderData->items[$relatedItem->getId()] = $relatedOrderItemData;
                $orderItemData->relatedOrderItemsData[] = $relatedOrderItemData;
            }

            $orderData->items[$orderItem->getId()] = $orderItemData;
        }
    }

    protected function fillZeroPrices(OrderData $orderData): OrderData
    {
        foreach ($this->orderItemTypeEnum->getAllCases() as $type) {
            $orderData->totalPricesByItemType[$type] = new Price(Money::zero(), Money::zero());
        }

        return $orderData;
    }
}

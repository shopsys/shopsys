<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum $orderItemTypeEnum
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     */
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory,
        protected readonly OrderItemTypeEnum $orderItemTypeEnum,
        protected readonly OrderInputFactory $orderInputFactory,
        protected readonly OrderProcessor $orderProcessor,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    protected function createInstance(): OrderData
    {
        return new OrderData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function create(): OrderData
    {
        $orderData = $this->createInstance();

        return $this->fillZeroPrices($orderData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function createFromOrder(Order $order): OrderData
    {
        $orderData = $this->create();

        $this->fillFromOrder($orderData, $order);

        return $orderData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function createFromCart(Cart $cart, DomainConfig $domainConfig): OrderData
    {
        $orderData = $this->create();

        return $this->fillFromCart($orderData, $cart, $domainConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function fillFromOrder(OrderData $orderData, Order $order): void
    {
        $orderData->orderNumber = $order->getNumber();
        $orderData->status = $order->getStatus();
        $orderData->firstName = $order->getFirstName();
        $orderData->lastName = $order->getLastName();
        $orderData->email = $order->getEmail();
        $orderData->telephone = $order->getTelephone();
        $orderData->companyName = $order->getCompanyName();
        $orderData->companyNumber = $order->getCompanyNumber();
        $orderData->companyTaxNumber = $order->getCompanyTaxNumber();
        $orderData->isCompanyCustomer = $order->isCompanyCustomer();
        $orderData->street = $order->getStreet();
        $orderData->city = $order->getCity();
        $orderData->postcode = $order->getPostcode();
        $orderData->country = $order->getCountry();
        $orderData->deliveryAddressSameAsBillingAddress = $order->isDeliveryAddressSameAsBillingAddress();

        if (!$order->isDeliveryAddressSameAsBillingAddress()) {
            $orderData->deliveryFirstName = $order->getDeliveryFirstName();
            $orderData->deliveryLastName = $order->getDeliveryLastName();
            $orderData->deliveryCompanyName = $order->getDeliveryCompanyName();
            $orderData->deliveryTelephone = $order->getDeliveryTelephone();
            $orderData->deliveryStreet = $order->getDeliveryStreet();
            $orderData->deliveryCity = $order->getDeliveryCity();
            $orderData->deliveryPostcode = $order->getDeliveryPostcode();
            $orderData->deliveryCountry = $order->getDeliveryCountry();
        }
        $orderData->note = $order->getNote();

        foreach ($order->getItems() as $orderItem) {
            $orderItemData = $this->orderItemDataFactory->createFromOrderItem($orderItem);
            $orderData->items[$orderItem->getId()] = $orderItemData;
        }
        $orderData->createdAt = $order->getCreatedAt();
        $orderData->domainId = $order->getDomainId();
        $orderData->currency = $order->getCurrency();
        $orderData->createdAsAdministrator = $order->getCreatedAsAdministrator();
        $orderData->createdAsAdministratorName = $order->getCreatedAsAdministratorName();
        $orderData->orderTransport = $this->orderItemDataFactory->createFromOrderItem($order->getTransportItem());
        $orderData->orderPayment = $this->orderItemDataFactory->createFromOrderItem($order->getPaymentItem());

        $orderData->goPayBankSwift = $order->getGoPayBankSwift();

        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $orderData->paymentTransactionRefunds[$paymentTransaction->getId()] = $this->paymentTransactionRefundDataFactory->createFromPaymentTransaction($paymentTransaction);
        }

        $orderData->heurekaAgreement = $order->isHeurekaAgreement();
        $orderData->trackingNumber = $order->getTrackingNumber();
        $orderData->promoCode = $order->getPromoCode();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    protected function fillZeroPrices(OrderData $orderData): OrderData
    {
        foreach ($this->orderItemTypeEnum->getAllCases() as $type) {
            $orderData->totalPricesByItemType[$type] = new Price(Money::zero(), Money::zero());
        }

        return $orderData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function fillFromCart(OrderData $orderData, Cart $cart, DomainConfig $domainConfig): OrderData
    {
        $orderInput = $this->orderInputFactory->createFromCart($cart, $domainConfig);

        return $this->orderProcessor->process(
            $orderInput,
            $orderData,
        );
    }
}

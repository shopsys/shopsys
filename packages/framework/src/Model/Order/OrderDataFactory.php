<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory;

class OrderDataFactory implements OrderDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactoryInterface $orderItemDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory
     */
    public function __construct(
        protected readonly OrderItemDataFactoryInterface $orderItemDataFactory,
        protected readonly PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory,
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
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public function createFromOrder(Order $order): OrderData
    {
        $orderData = $this->createInstance();
        $this->fillFromOrder($orderData, $order);

        return $orderData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    protected function fillFromOrder(OrderData $orderData, Order $order)
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
        $orderItemsWithoutTransportAndPaymentData = [];

        foreach ($order->getItemsWithoutTransportAndPayment() as $orderItem) {
            $orderItemData = $this->orderItemDataFactory->createFromOrderItem($orderItem);
            $orderItemsWithoutTransportAndPaymentData[$orderItem->getId()] = $orderItemData;
        }
        $orderData->itemsWithoutTransportAndPayment = $orderItemsWithoutTransportAndPaymentData;
        $orderData->createdAt = $order->getCreatedAt();
        $orderData->domainId = $order->getDomainId();
        $orderData->currency = $order->getCurrency();
        $orderData->createdAsAdministrator = $order->getCreatedAsAdministrator();
        $orderData->createdAsAdministratorName = $order->getCreatedAsAdministratorName();
        $orderData->orderTransport = $this->orderItemDataFactory->createFromOrderItem($order->getOrderTransport());
        $orderData->orderPayment = $this->orderItemDataFactory->createFromOrderItem($order->getOrderPayment());

        $orderData->goPayBankSwift = $order->getGoPayBankSwift();

        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $orderData->paymentTransactionRefunds[$paymentTransaction->getId()] = $this->paymentTransactionRefundDataFactory->createFromPaymentTransaction($paymentTransaction);
        }

        $orderData->heurekaAgreement = $order->isHeurekaAgreement();
    }
}

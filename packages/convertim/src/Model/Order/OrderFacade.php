<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Order;

use Convertim\Order\ConvertimOrderData;
use Convertim\Order\ConvertimOrderDataPaymentStatus;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;

class OrderFacade
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Order\ConvertimOrderDataToOrderDataMapper $convertimOrderDataToOrderMapper
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\PlaceOrderFacade $placeOrderFacade
     * @param \Shopsys\ConvertimBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade $paymentTransactionFacade
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory $paymentTransactionDataFactory
     */
    public function __construct(
        protected readonly ConvertimOrderDataToOrderDataMapper $convertimOrderDataToOrderMapper,
        protected readonly Domain $domain,
        protected readonly PlaceOrderFacade $placeOrderFacade,
        protected readonly OrderRepository $orderRepository,
        protected readonly PaymentTransactionFacade $paymentTransactionFacade,
        protected readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
    ) {
    }

    /**
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function saveOrder(ConvertimOrderData $convertimOrderData): Order
    {
        $order = $this->orderRepository->findByConvertimUuid($convertimOrderData->getUuid());

        if ($order === null) {
            $orderData = $this->convertimOrderDataToOrderMapper->mapConvertimOrderDataToOrderData($convertimOrderData);
            $deliveryAddressUuid = $convertimOrderData->getCustomerData()->getConvertimCustomerDeliveryAddressData()->getUuid();

            $order = $this->placeOrderFacade->placeOrder($orderData, $deliveryAddressUuid);
        }

        if ($order->getPayment()->isGoPay()) {
            $this->resolveGoPayStatus($order, $convertimOrderData);
        }

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Convertim\Order\ConvertimOrderData $convertimOrderData
     */
    protected function resolveGoPayStatus(Order $order, ConvertimOrderData $convertimOrderData): void
    {
        $convertimPaymentStatus = $convertimOrderData->getPaymentStatus();

        if ($convertimPaymentStatus === null || $convertimOrderData->getGoPayData() === null) {
            return;
        }

        $paymentTransactions = $order->getPaymentTransactions();
        $externalPaymentId = $convertimPaymentStatus->getPaymentProviderId();

        $currentPaymentTransaction = null;

        if (count($paymentTransactions) > 0) {
            foreach ($paymentTransactions as $paymentTransaction) {
                if ($paymentTransaction->getExternalPaymentIdentifier() === $externalPaymentId) {
                    $currentPaymentTransaction = $paymentTransaction;

                    if ($paymentTransaction->getExternalPaymentStatus() === $convertimPaymentStatus->getStatus()) {
                        return;
                    }
                }
            }
        }

        if ($currentPaymentTransaction === null) {
            $this->createPaymentTransaction($order, $convertimPaymentStatus, $order->getTotalPriceWithVat());
        } else {
            $this->updatePaymentTransaction($currentPaymentTransaction, $convertimPaymentStatus);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Convertim\Order\ConvertimOrderDataPaymentStatus $convertimOrderDataPaymentStatus
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $paidAmount
     */
    protected function createPaymentTransaction(
        Order $order,
        ConvertimOrderDataPaymentStatus $convertimOrderDataPaymentStatus,
        Money $paidAmount,
    ): void {
        $paymentTransactionData = $this->paymentTransactionDataFactory->create();
        $paymentTransactionData->order = $order;
        $paymentTransactionData->payment = $order->getPayment();
        $paymentTransactionData->externalPaymentIdentifier = $convertimOrderDataPaymentStatus->getPaymentProviderId();
        $paymentTransactionData->externalPaymentStatus = $convertimOrderDataPaymentStatus->getStatus();
        $paymentTransactionData->paidAmount = $paidAmount;

        $this->paymentTransactionFacade->create($paymentTransactionData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @param \Convertim\Order\ConvertimOrderDataPaymentStatus $convertimOrderDataPaymentStatus
     */
    protected function updatePaymentTransaction(
        PaymentTransaction $paymentTransaction,
        ConvertimOrderDataPaymentStatus $convertimOrderDataPaymentStatus,
    ): void {
        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
        $paymentTransactionData->externalPaymentStatus = $convertimOrderDataPaymentStatus->getStatus();

        $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
    }
}

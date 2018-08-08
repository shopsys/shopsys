<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

class OrderPaymentDataFactory implements OrderPaymentDataFactoryInterface
{
    public function create(): OrderPaymentData
    {
        return new OrderPaymentData();
    }

    public function createFromOrderPayment(OrderPayment $orderPayment): OrderPaymentData
    {
        $orderPaymentData = new OrderPaymentData();
        $this->fillFromOrderPayment($orderPaymentData, $orderPayment);

        return $orderPaymentData;
    }

    protected function fillFromOrderPayment(OrderPaymentData $orderPaymentData, OrderPayment $orderPayment): void
    {
        $orderPaymentData->name = $orderPayment->getName();
        $orderPaymentData->priceWithVat = $orderPayment->getPriceWithVat();
        $orderPaymentData->priceWithoutVat = $orderPayment->getPriceWithoutVat();
        $orderPaymentData->vatPercent = $orderPayment->getVatPercent();
        $orderPaymentData->quantity = $orderPayment->getQuantity();
        $orderPaymentData->unitName = $orderPayment->getUnitName();
        $orderPaymentData->catnum = $orderPayment->getCatnum();
        $orderPaymentData->payment = $orderPayment->getPayment();
    }
}

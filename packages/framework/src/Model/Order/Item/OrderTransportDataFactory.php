<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

class OrderTransportDataFactory implements OrderTransportDataFactoryInterface
{
    public function create(): OrderTransportData
    {
        return new OrderTransportData();
    }

    public function createFromOrderTransport(OrderTransport $orderTransport): OrderTransportData
    {
        $orderTransportData = new OrderTransportData();
        $this->fillFromOrderTransport($orderTransportData, $orderTransport);

        return $orderTransportData;
    }

    protected function fillFromOrderTransport(OrderTransportData $orderTransportData, OrderTransport $orderTransport): void
    {
        $orderTransportData->name = $orderTransport->getName();
        $orderTransportData->priceWithVat = $orderTransport->getPriceWithVat();
        $orderTransportData->priceWithoutVat = $orderTransport->getPriceWithoutVat();
        $orderTransportData->vatPercent = $orderTransport->getVatPercent();
        $orderTransportData->quantity = $orderTransport->getQuantity();
        $orderTransportData->unitName = $orderTransport->getUnitName();
        $orderTransportData->catnum = $orderTransport->getCatnum();
        $orderTransportData->transport = $orderTransport->getTransport();
    }
}

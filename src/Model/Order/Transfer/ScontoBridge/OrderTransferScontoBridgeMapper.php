<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use App\Model\Product\Transfer\ScontoBridge\Mapper\Entity\ScontoBridgeErpOrder;

class OrderTransferScontoBridgeMapper
{
    public function mapOrderToScontoBridgeOrderData(Order $order): ScontoBridgeErpOrder
    {
        $erpOrder = new ScontoBridgeErpOrder();
        $erpOrder->setEshopId($order->getId());
        $erpOrder->setEshopOrderNumber($order->getNumber());
        $erpOrder->setDistributionChannelId(421);//todo
        $customerUser = $order->getCustomerUser();
        if ($customerUser === null) {
            throw new \Exception(sprintf('No customer defined for order no %s', $order->getNumber()));//fixme
        }
        $erpOrder->setEshopUserId($customerUser->getId());
        $erpOrder->setCreationTime($order->getCreatedAt()->format('c')); //todo
        $erpOrder->setPriceWithVat((float)$order->getTotalPriceWithVat()->getAmount());
        $erpOrder->setPriceCurrency($order->getCurrency()->getCode());
        $erpOrder->setTitle($customerUser->getGender());
        $erpOrder->setFirstName($order->getFirstName());
        $erpOrder->setLastName($order->getLastName());

        $erpOrder->setInvoiceAddressStreet($order->getStreet());
        $erpOrder->setInvoiceAddressCountryISO('CZ'); //todo
        $erpOrder->setInvoiceAddressCity($order->getCity());
        $erpOrder->setInvoiceAddressZipCode($order->getPostcode());

        $erpOrder->setPhone($order->getTelephone());
        $erpOrder->setEmail($order->getEmail());

        $erpOrder->setPaymentMethodId($order->getPayment()->getId()); //todo
        $erpOrder->setDeliveryMethodId($order->getTransport()->getId()); //fixme - kterou vyvbrat?

        $erpOrder->setDeliveryAddressFirstName($order->getDeliveryFirstName());
        $erpOrder->setDeliveryAddressLastName($order->getDeliveryLastName());
        $erpOrder->setDeliveryAddressStreet($order->getDeliveryStreet());
        $erpOrder->setDeliveryAddressCity($order->getDeliveryCity());
        $erpOrder->setDeliveryAddressCountryISO('CZ'); //todo
        $erpOrder->setDeliveryAddressZipCode($order->getDeliveryPostcode());
        $erpOrder->setDeliveryAddressPhone($order->getDeliveryTelephone());

        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->isTypeTransport()) {
                if ($orderItem->getPersonalPickupStock() !== null) {
                    $erpOrder->setCollectionStoreCode($orderItem->getPersonalPickupStock()->getExternalId());
                }
            }

            $erpOrder->addItem($this->mapOrderItem($orderItem));
        }

        return $erpOrder;
    }

    private function mapOrderItem(OrderItem $orderItem): ScontoBridgeErpOrder\ScontoBridgeOrderItem
    {
        $erpOrderItem = new ScontoBridgeErpOrder\ScontoBridgeOrderItem();
        $erpOrderItem->setEshopId($orderItem->getProduct()->getId());
        //$erpOrderItem->setStoreCode(); //bude odstraneno
        $erpOrderItem->setSku($orderItem->getCatnum());
        $erpOrderItem->setQuantity($orderItem->getQuantity());
        $erpOrderItem->setUnitPriceWithVat((float)$orderItem->getPriceWithVat()->getAmount());
        $erpOrderItem->setPriceWithVat((float)$orderItem->getTotalPriceWithVat());
        $erpOrderItem->setType($this->resolveType($orderItem)); //ciselnik
        $erpOrderItem->setPromocodeIdentifier($orderItem->getPromoCodeIdentifier());

        return $erpOrderItem;
    }

    /**
     * fixme
     * @param OrderItem $orderItem
     * @return int
     */
    private function resolveType(OrderItem $orderItem): int
    {
        if ($orderItem->isTypeProduct()) {
            return 1;
        }
        if ($orderItem->isTypeTransport()) {
            return 2;
        }
        if ($orderItem->isTypePayment()) {
            return 3;
        }

        return 0;
    }
}

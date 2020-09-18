<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use App\Component\ScontoBridge\Transfer\Exception\ScontoBridgeDistributionChannelResolverException;
use App\Component\ScontoBridge\Transfer\ScontoBridgeDistributionChannelResolver;
use App\Component\ScontoBridge\Transfer\ScontoBridgeTitleResolver;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use App\Model\Order\Transfer\ScontoBridge\Entity\ScontoBridgeErpOrder;
use App\Model\Order\Transfer\ScontoBridge\Entity\ScontoBridgeErpOrder\ScontoBridgeOrderItem;
use Shopsys\FrameworkBundle\Model\Country\Country;

class OrderTransferScontoBridgeMapper
{
    private const ORDER_ITEM_BRIDGE_TYPE = [
        OrderItem::TYPE_PRODUCT => 1,
        OrderItem::TYPE_PAYMENT => 2,
        OrderItem::TYPE_TRANSPORT => 3,
    ];

    /**
     * @var ScontoBridgeDistributionChannelResolver
     */
    private ScontoBridgeDistributionChannelResolver $scontoBridgeDistributionChannelResolver;

    /**
     * @var ScontoBridgeTitleResolver
     */
    private ScontoBridgeTitleResolver $scontoBridgeTitleResolver;

    /**
     * @param ScontoBridgeDistributionChannelResolver $scontoBridgeDistributionChannelResolver
     * @param ScontoBridgeTitleResolver $scontoBridgeTitleResolver
     */
    public function __construct(
        ScontoBridgeDistributionChannelResolver $scontoBridgeDistributionChannelResolver,
        ScontoBridgeTitleResolver $scontoBridgeTitleResolver
    ) {
        $this->scontoBridgeDistributionChannelResolver = $scontoBridgeDistributionChannelResolver;
        $this->scontoBridgeTitleResolver = $scontoBridgeTitleResolver;
    }

    /**
     * @param Order $order
     * @return ScontoBridgeErpOrder
     */
    public function mapOrderToScontoBridgeOrderData(Order $order): ScontoBridgeErpOrder
    {
        $erpOrder = new ScontoBridgeErpOrder();
        $erpOrder->setEshopId($order->getId());
        $erpOrder->setEshopOrderNumber($order->getNumber());
        $erpOrder->setDistributionChannelCode(
            $this->getDistributionChannelCode($order->getCountry())
        );
        $customerUser = $order->getCustomerUser();
        if ($customerUser === null) {
            throw new OrderTransferScontoBridgeMapperException(
                sprintf('No customer user defined for order no \'%s\'', $order->getNumber())
            );
        }
        $erpOrder->setEshopUserId($customerUser->getId());
        $erpOrder->setCreationTime($order->getCreatedAt()->format('c')); //todo domluvit jak s mikrosekundama
        $erpOrder->setPriceWithVat((float)$order->getTotalPriceWithVat()->getAmount());
        $erpOrder->setPriceCurrency($order->getCurrency()->getCode());

        $this->fillCustomerDetails($erpOrder, $order, $customerUser);
        $this->fillInvoiceAddress($erpOrder, $order);
        $this->fillDeliveryAddress($erpOrder, $order);

        $erpOrder->setPaymentMethodId($order->getPayment()->getExternalId());

        $transport = $order->getTransport();
        $erpOrder->setDeliveryMethodId($transport->getExternalId());
        $erpOrder->setDeliveryCode($transport->getDeliveryCode());
        $erpOrder->setTypeOfDeliveryKey($transport->getTypeOfDeliveryKey());

        $this->fillOrderItems($erpOrder, $order);

        return $erpOrder;
    }

    /**
     * @param Country $country
     * @return int
     */
    private function getDistributionChannelCode(Country $country): int
    {
        try {
            return $this->scontoBridgeDistributionChannelResolver->getDistributionChannelCodeByCountry($country);
        } catch (ScontoBridgeDistributionChannelResolverException $e) {
            throw new OrderTransferScontoBridgeMapperException($e->getMessage(), $e);
        }
    }

    /**
     * @param ScontoBridgeErpOrder $erpOrder
     * @param Order $order
     * @param CustomerUser $customerUser
     */
    private function fillCustomerDetails(ScontoBridgeErpOrder $erpOrder, Order $order, CustomerUser $customerUser): void
    {
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();
        if ($billingAddress->isCompanyCustomer() === false) {
            $title = $this->scontoBridgeTitleResolver->getIndividualTitleByGender($customerUser->getGender());
            if ($title !== null) {
                $erpOrder->setTitle($title);
            }
            $erpOrder->setInvoiceAddressFirstName($order->getFirstName());
            $erpOrder->setInvoiceAddressLastName($order->getLastName());
        }
        $erpOrder->setInvoiceAddressPhone($order->getTelephone());
        $erpOrder->setEmail($order->getEmail());
    }

    /**
     * @param ScontoBridgeErpOrder $erpOrder
     * @param Order $order
     */
    private function fillInvoiceAddress(ScontoBridgeErpOrder $erpOrder, Order $order): void
    {
        $companyName = $order->getCompanyName();
        if ($companyName !== null) {
            $erpOrder->setInvoiceAddressCompanyName($companyName);
        }
        $erpOrder->setInvoiceAddressStreet($order->getStreet());
        $erpOrder->setInvoiceAddressCountryISO($order->getCountry()->getCode());
        $erpOrder->setInvoiceAddressCity($order->getCity());
        $erpOrder->setInvoiceAddressZipCode($order->getPostcode());
    }

    /**
     * @param ScontoBridgeErpOrder $erpOrder
     * @param Order $order
     */
    private function fillDeliveryAddress(ScontoBridgeErpOrder $erpOrder, Order $order): void
    {
        /** @var string|null $deliveryCompanyName */
        $deliveryCompanyName = $order->getDeliveryCompanyName();
        if ($deliveryCompanyName === null) {
            $erpOrder->setDeliveryAddressFirstName($order->getDeliveryFirstName());
            $erpOrder->setDeliveryAddressLastName($order->getDeliveryLastName());
        } else {
            $erpOrder->setDeliveryAddressCompanyName($deliveryCompanyName);
        }
        $erpOrder->setDeliveryAddressStreet($order->getDeliveryStreet());
        $erpOrder->setDeliveryAddressCity($order->getDeliveryCity());
        $country = $order->getDeliveryCountry();
        if ($country !== null) {
            $erpOrder->setDeliveryAddressCountryISO($country->getCode());
        }
        $erpOrder->setDeliveryAddressZipCode($order->getDeliveryPostcode());
        $erpOrder->setDeliveryAddressPhone($order->getDeliveryTelephone());
    }

    /**
     * @param ScontoBridgeErpOrder $erpOrder
     * @param Order $order
     */
    private function fillOrderItems(ScontoBridgeErpOrder $erpOrder, Order $order): void
    {
        foreach ($order->getItems() as $orderItem) {
            if ($orderItem->isTypeTransport()) {
                if ($orderItem->getPersonalPickupStock() !== null) {
                    $erpOrder->setCollectionStoreCode($orderItem->getPersonalPickupStock()->getExternalId());
                }
            }

            $orderItemPriceIsNonzero = $orderItem->getTotalPriceWithVat()->getAmount() > 0;
            $orderItemIsTransportOrPaymentWithNonzeroPrice = $orderItemPriceIsNonzero &&
                ($orderItem->isTypeTransport() || $orderItem->isTypePayment());

            if ($orderItemIsTransportOrPaymentWithNonzeroPrice || $orderItem->isTypeProduct()) {
                $erpOrder->addItem($this->mapOrderItem($orderItem));
            }
        }
    }

    /**
     * @param OrderItem $orderItem
     * @return ScontoBridgeOrderItem
     */
    private function mapOrderItem(OrderItem $orderItem): ScontoBridgeOrderItem
    {
        $erpOrderItem = new ScontoBridgeOrderItem();
        $erpOrderItem->setEshopId($orderItem->getId());
        $erpOrderItem->setQuantity($orderItem->getQuantity());
        $erpOrderItem->setUnitPriceWithVat((float)$orderItem->getPriceWithVat()->getAmount());
        $erpOrderItem->setPriceWithVat((float)$orderItem->getTotalPriceWithVat()->getAmount());
        $erpOrderItem->setType($this->resolveType($orderItem));
        $personalPickupStore = $orderItem->getPersonalPickupStock();
        if ($personalPickupStore !== null) {
            $erpOrderItem->setStoreCode($personalPickupStore->getExternalId());
        }
        $promoCode = $orderItem->getPromoCodeIdentifier();
        if ($promoCode !== null) {
            $erpOrderItem->setPromocodeIdentifier($promoCode);
        }
        $sku = $orderItem->getCatnum();
        if ($sku !== null && $orderItem->isTypeProduct()) {
            $erpOrderItem->setSku($sku);
        } elseif ($orderItem->isTypePayment()) {
            $erpOrderItem->setSku((string)$orderItem->getPayment()->getExternalId());
        } elseif ($orderItem->isTypeTransport()) {
            $erpOrderItem->setSku((string)$orderItem->getTransport()->getExternalId());
        }

        return $erpOrderItem;
    }

    /**
     * @param OrderItem $orderItem
     * @return int
     */
    private function resolveType(OrderItem $orderItem): int
    {
        $type = $orderItem->getType();
        if (array_key_exists($type, self::ORDER_ITEM_BRIDGE_TYPE) === false) {
            throw new OrderTransferScontoBridgeMapperException(
                sprintf('Invalid order item type \'%s\'', $type)
            );
        }

        return self::ORDER_ITEM_BRIDGE_TYPE[$type];
    }
}

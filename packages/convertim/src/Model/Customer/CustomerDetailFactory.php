<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Customer;

use Convertim\Customer\BillingAddress as ConvertimBillingAddress;
use Convertim\Customer\CustomerDetail;
use Convertim\Customer\DeliveryAddress as ConvertimDeliveryAddress;
use Convertim\Customer\LastSelectedPickupPoint;
use Convertim\Transport\ConvertimTransportSources;
use Shopsys\ConvertimBundle\Model\Customer\Exception\CustomerDetailsNotFoundException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerUserNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as FrameworkCustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;

class CustomerDetailFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly FrameworkCustomerUserFacade $customerUserFacade,
        protected readonly Domain $domain,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @param string $userUuid
     * @return \Convertim\Customer\CustomerDetail
     */
    public function createCustomerDetail(string $userUuid): CustomerDetail
    {
        try {
            $customerUser = $this->customerUserFacade->getByUuid($userUuid);
            $telephone = $customerUser->getTelephone();
            $lastOrders = $this->orderFacade->getLastCustomerOrdersByLimit($customerUser->getCustomer(), 1, $this->domain->getLocale());
            /** @var \Shopsys\FrameworkBundle\Model\Order\Order|null $lastOrder */
            $lastOrder = count($lastOrders) > 0 ? reset($lastOrders) : null;

            $billingAddress = $customerUser->getCustomer()->getBillingAddress();
            $deliveryAddresses = $customerUser->getCustomer()->getDeliveryAddresses();

            if (count($deliveryAddresses) > 0) {
                /** @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $lastDeliveryAddress */
                $lastDeliveryAddress = end($deliveryAddresses);

                $deliveryAddress = $this->createDeliveryAddressFromDeliveryAddress($lastDeliveryAddress, $customerUser);

                if ($telephone === null) {
                    $telephone = $lastDeliveryAddress->getTelephone();
                }
            } else {
                if ($billingAddress->getStreet() === null) {
                    throw new CustomerDetailsNotFoundException(['customerUserUuid' => $userUuid]);
                }

                $deliveryAddress = $this->createDeliveryAddressFromBillingAddress($billingAddress, $customerUser);
            }

            return new CustomerDetail(
                $customerUser->getEmail(),
                $telephone ?: '',
                $deliveryAddress,
                $this->createBillingAddress($billingAddress, $customerUser),
                $lastOrder?->getPayment()->getUuid(),
                $lastOrder?->getTransport()->getUuid(),
                $this->createLastSelectedPickupPoint($lastOrder),
            );
        } catch (CustomerUserNotFoundException) {
            throw new CustomerDetailsNotFoundException(['customerUserUuid' => $userUuid]);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order|null $order
     * @return \Convertim\Customer\LastSelectedPickupPoint|null
     */
    protected function createLastSelectedPickupPoint(?Order $order): ?LastSelectedPickupPoint
    {
        if ($order === null) {
            return null;
        }

        if ($order->getTransport()->getType() === TransportTypeEnum::TYPE_PACKETERY) {
            return new LastSelectedPickupPoint(
                ConvertimTransportSources::SOURCE_PACKETA,
                $order->getPickupPlaceIdentifier(),
            );
        }

        if ($order->getTransport()->getType() === TransportTypeEnum::TYPE_PERSONAL_PICKUP) {
            return new LastSelectedPickupPoint(
                ConvertimTransportSources::SOURCE_STORES,
                $order->getPickupPlaceIdentifier(),
            );
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress|null $billingAddress
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Convertim\Customer\BillingAddress|null
     */
    protected function createBillingAddress(
        ?BillingAddress $billingAddress,
        CustomerUser $customerUser,
    ): ?ConvertimBillingAddress {
        if ($billingAddress === null || $billingAddress->getStreet() === null) {
            return null;
        }

        return new ConvertimBillingAddress(
            (string)$billingAddress->getId(),
            $customerUser->getFirstName(),
            $customerUser->getLastName(),
            $billingAddress->getStreet(),
            $billingAddress->getCity(),
            $billingAddress->getPostcode(),
            $billingAddress->getCountry()->getCode(),
            $billingAddress->getCompanyName(),
            $billingAddress->getCompanyNumber(),
            $billingAddress->getCompanyTaxNumber(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $lastDeliveryAddress
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Convertim\Customer\DeliveryAddress
     */
    protected function createDeliveryAddressFromDeliveryAddress(
        DeliveryAddress $lastDeliveryAddress,
        CustomerUser $customerUser,
    ): ConvertimDeliveryAddress {
        return new ConvertimDeliveryAddress(
            (string)$lastDeliveryAddress->getId(),
            $customerUser->getFirstName(),
            $customerUser->getLastName(),
            $lastDeliveryAddress->getStreet(),
            $lastDeliveryAddress->getCity(),
            $lastDeliveryAddress->getPostcode(),
            $lastDeliveryAddress->getCountry()->getCode(),
            $lastDeliveryAddress->getCompanyName(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Convertim\Customer\DeliveryAddress
     */
    protected function createDeliveryAddressFromBillingAddress(
        BillingAddress $billingAddress,
        CustomerUser $customerUser,
    ): ConvertimDeliveryAddress {
        return new ConvertimDeliveryAddress(
            (string)$billingAddress->getId(),
            $customerUser->getFirstName(),
            $customerUser->getLastName(),
            $billingAddress->getStreet(),
            $billingAddress->getCity(),
            $billingAddress->getPostcode(),
            $billingAddress->getCountry()?->getCode(),
            $billingAddress->getCompanyName(),
        );
    }

    /**
     * @param string $orderUuid
     * @return \Convertim\Customer\CustomerDetail
     */
    public function createCustomerDetailByOrderUuid(string $orderUuid): CustomerDetail
    {
        try {
            $order = $this->orderFacade->getByUuid($orderUuid);

            $billingAddress = null;

            if ($order->isDeliveryAddressSameAsBillingAddress()) {
                $deliveryAddress = $this->createDeliveryAddressFromOrderBillingAddress($order);
            } else {
                $deliveryAddress = $this->createDeliveryAddressFromOrderDeliveryAddress($order);

                if ($order->getStreet()) {
                    $billingAddress = $this->createBillingAddressFromOrder($order);
                }
            }

            return new CustomerDetail(
                $order->getEmail(),
                $order->getTelephone(),
                $deliveryAddress,
                $billingAddress,
                $order->getPayment()->getUuid(),
                $order->getTransport()->getUuid(),
                $this->createLastSelectedPickupPoint($order),
            );
        } catch (OrderNotFoundException) {
            throw new CustomerDetailsNotFoundException(['orderUuid' => $orderUuid]);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return bool
     */
    protected function isOrderPersonalPickup(Order $order): bool
    {
        return in_array($order->getTransport()->getType(), [TransportTypeEnum::TYPE_PERSONAL_PICKUP, TransportTypeEnum::TYPE_PACKETERY], true);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Convertim\Customer\DeliveryAddress
     */
    protected function createDeliveryAddressFromOrderBillingAddress(Order $order): ConvertimDeliveryAddress
    {
        $isOrderPersonalPickup = $this->isOrderPersonalPickup($order);

        return new ConvertimDeliveryAddress(
            '',
            $order->getFirstName(),
            $order->getLastName(),
            $isOrderPersonalPickup ? '' : $order->getStreet(),
            $isOrderPersonalPickup ? '' : $order->getCity(),
            $isOrderPersonalPickup ? '' : $order->getPostcode(),
            $isOrderPersonalPickup ? '' : $order->getCountry()->getCode(),
            $isOrderPersonalPickup ? '' : $order->getCompanyName(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Convertim\Customer\DeliveryAddress
     */
    protected function createDeliveryAddressFromOrderDeliveryAddress(Order $order): ConvertimDeliveryAddress
    {
        $isOrderPersonalPickup = $this->isOrderPersonalPickup($order);

        return new ConvertimDeliveryAddress(
            '',
            $order->getFirstName(),
            $order->getLastName(),
            $isOrderPersonalPickup ? '' : $order->getDeliveryStreet(),
            $isOrderPersonalPickup ? '' : $order->getDeliveryCity(),
            $isOrderPersonalPickup ? '' : $order->getDeliveryPostcode(),
            $isOrderPersonalPickup ? '' : $order->getDeliveryCountry()?->getCode(),
            $isOrderPersonalPickup ? '' : $order->getDeliveryCompanyName(),
            $order->getDeliveryTelephone(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Convertim\Customer\BillingAddress
     */
    private function createBillingAddressFromOrder(Order $order): ConvertimBillingAddress
    {
        return new ConvertimBillingAddress(
            '',
            $order->getFirstName(),
            $order->getLastName(),
            $order->getStreet(),
            $order->getCity(),
            $order->getPostcode(),
            $order->getCountry()->getCode(),
            $order->getCompanyName(),
            $order->getCompanyNumber(),
            $order->getCompanyTaxNumber(),
        );
    }
}

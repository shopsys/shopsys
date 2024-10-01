<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserUpdateDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface $customerUserDataFactory
     */
    public function __construct(
        protected readonly BillingAddressDataFactoryInterface $billingAddressDataFactory,
        protected readonly DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory,
        protected readonly CustomerUserDataFactoryInterface $customerUserDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    protected function createInstance(
        BillingAddressData $billingAddressData,
        DeliveryAddressData $deliveryAddressData,
        CustomerUserData $customerUserData,
    ): CustomerUserUpdateData {
        return new CustomerUserUpdateData($billingAddressData, $customerUserData, $deliveryAddressData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function create(): CustomerUserUpdateData
    {
        return $this->createInstance(
            $this->billingAddressDataFactory->create(),
            $this->deliveryAddressDataFactory->create(),
            $this->customerUserDataFactory->create(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $password
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromOrder(Order $order, string $password): CustomerUserUpdateData
    {
        $customerUserUpdateData = $this->create();

        $customerUserUpdateData->customerUserData = $this->getCustomerUserDataByOrder($order, $password);
        $this->fillBillingAddressDataFromOrder($order, $customerUserUpdateData->billingAddressData);

        $transport = $order->getTransport();

        if (
            !$transport->isPersonalPickup() &&
            !$transport->isPacketery()
        ) {
            $customerUserUpdateData->deliveryAddressData = $this->getAmendedDeliveryAddressDataByOrder($order);
        }

        return $customerUserUpdateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserUpdateData
    {
        return $this->createInstance(
            $this->billingAddressDataFactory->createFromBillingAddress(
                $customerUser->getCustomer()->getBillingAddress(),
            ),
            $this->getDeliveryAddressDataFromCustomerUser($customerUser),
            $this->customerUserDataFactory->createFromCustomerUser($customerUser),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    protected function getDeliveryAddressDataFromCustomerUser(CustomerUser $customerUser): DeliveryAddressData
    {
        if ($customerUser->getDefaultDeliveryAddress() !== null) {
            return $this->deliveryAddressDataFactory->createFromDeliveryAddress(
                $customerUser->getDefaultDeliveryAddress(),
            );
        }

        return $this->deliveryAddressDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createAmendedByOrder(
        CustomerUser $customerUser,
        Order $order,
        ?DeliveryAddress $deliveryAddress,
    ): CustomerUserUpdateData {
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();

        $customerUserUpdateData = $this->createFromCustomerUser($customerUser);

        $customerUserUpdateData->customerUserData->firstName = Utils::ifNull(
            $customerUser->getFirstName(),
            $order->getFirstName(),
        );
        $customerUserUpdateData->customerUserData->lastName = Utils::ifNull(
            $customerUser->getLastName(),
            $order->getLastName(),
        );
        $customerUserUpdateData->customerUserData->telephone = Utils::ifNull(
            $customerUser->getTelephone(),
            $order->getTelephone(),
        );
        $customerUserUpdateData->billingAddressData = $this->getAmendedBillingAddressDataByOrder(
            $order,
            $billingAddress,
        );

        $transport = $order->getTransport();

        if (
            !$transport->isPersonalPickup() &&
            !$transport->isPacketery()
        ) {
            $customerUserUpdateData->deliveryAddressData = $this->getAmendedDeliveryAddressDataByOrder(
                $order,
                $deliveryAddress,
            );
        }

        return $customerUserUpdateData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    protected function getAmendedBillingAddressDataByOrder(Order $order, BillingAddress $billingAddress)
    {
        $billingAddressData = $this->billingAddressDataFactory->createFromBillingAddress($billingAddress);

        if ($billingAddress->getStreet() === null) {
            $this->fillBillingAddressDataFromOrder($order, $billingAddressData);
        }

        return $billingAddressData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    protected function getAmendedDeliveryAddressDataByOrder(Order $order, ?DeliveryAddress $deliveryAddress = null)
    {
        if ($deliveryAddress === null) {
            $deliveryAddressData = $this->deliveryAddressDataFactory->create();
            $deliveryAddressData->addressFilled = !$order->isDeliveryAddressSameAsBillingAddress();
            $deliveryAddressData->street = $order->getDeliveryStreet();
            $deliveryAddressData->city = $order->getDeliveryCity();
            $deliveryAddressData->postcode = $order->getDeliveryPostcode();
            $deliveryAddressData->country = $order->getDeliveryCountry();
            $deliveryAddressData->companyName = $order->getDeliveryCompanyName();
            $deliveryAddressData->firstName = $order->getDeliveryFirstName();
            $deliveryAddressData->lastName = $order->getDeliveryLastName();
            $deliveryAddressData->telephone = $order->getDeliveryTelephone();
        } else {
            $deliveryAddressData = $this->deliveryAddressDataFactory->createFromDeliveryAddress($deliveryAddress);
        }

        if ($deliveryAddress !== null && $deliveryAddress->getTelephone() === null) {
            $deliveryAddressData->telephone = $order->getTelephone();
        }

        return $deliveryAddressData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     */
    protected function fillBillingAddressDataFromOrder(
        Order $order,
        BillingAddressData $billingAddressData,
    ): void {
        $billingAddressData->companyCustomer = $order->isCompanyCustomer();
        $billingAddressData->companyName = $order->getCompanyName();
        $billingAddressData->companyNumber = $order->getCompanyNumber();
        $billingAddressData->companyTaxNumber = $order->getCompanyTaxNumber();
        $billingAddressData->street = $order->getStreet();
        $billingAddressData->city = $order->getCity();
        $billingAddressData->postcode = $order->getPostcode();
        $billingAddressData->country = $order->getCountry();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param string $password
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    protected function getCustomerUserDataByOrder(Order $order, string $password): CustomerUserData
    {
        $customerUserData = $this->customerUserDataFactory->createForDomainId($order->getDomainId());
        $customerUserData->firstName = $order->getFirstName();
        $customerUserData->lastName = $order->getLastName();
        $customerUserData->telephone = $order->getTelephone();
        $customerUserData->email = $order->getEmail();
        $customerUserData->password = $password;

        return $customerUserData;
    }
}

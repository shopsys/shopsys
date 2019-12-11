<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Order\Order;

class CustomerUserDataFactory implements CustomerUserDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface
     */
    protected $billingAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface
     */
    protected $deliveryAddressDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface
     */
    protected $userDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface
     */
    protected $customerFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactoryInterface $billingAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserDataFactoryInterface $userDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface $customerFactory
     */
    public function __construct(
        BillingAddressDataFactoryInterface $billingAddressDataFactory,
        DeliveryAddressDataFactoryInterface $deliveryAddressDataFactory,
        UserDataFactoryInterface $userDataFactory,
        CustomerFactoryInterface $customerFactory
    ) {
        $this->billingAddressDataFactory = $billingAddressDataFactory;
        $this->deliveryAddressDataFactory = $deliveryAddressDataFactory;
        $this->userDataFactory = $userDataFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function create(): CustomerUserData
    {
        $customer = $this->customerFactory->create();

        return new CustomerUserData(
            $this->billingAddressDataFactory->createForCustomer($customer),
            $this->deliveryAddressDataFactory->create(),
            $this->userDataFactory->createForCustomer($customer)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createFromUser(User $user): CustomerUserData
    {
        $customerUserData = new CustomerUserData(
            $this->billingAddressDataFactory->createFromBillingAddress($user->getCustomer()->getBillingAddress()),
            $this->getDeliveryAddressDataFromUser($user),
            $this->userDataFactory->createFromUser($user)
        );

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    protected function getDeliveryAddressDataFromUser(User $user): DeliveryAddressData
    {
        if ($user->getDeliveryAddress()) {
            return $this->deliveryAddressDataFactory->createFromDeliveryAddress($user->getDeliveryAddress());
        }

        return $this->deliveryAddressDataFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createAmendedByOrder(User $user, Order $order): CustomerUserData
    {
        $billingAddress = $user->getCustomer()->getBillingAddress();
        $deliveryAddress = $user->getDeliveryAddress();

        $customerUserData = $this->createFromUser($user);

        $customerUserData->userData->firstName = Utils::ifNull($user->getFirstName(), $order->getFirstName());
        $customerUserData->userData->lastName = Utils::ifNull($user->getLastName(), $order->getLastName());
        $customerUserData->billingAddressData = $this->getAmendedBillingAddressDataByOrder($order, $billingAddress);
        $customerUserData->deliveryAddressData = $this->getAmendedDeliveryAddressDataByOrder($order, $deliveryAddress);

        return $customerUserData;
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
            $billingAddressData->companyCustomer = $order->getCompanyNumber() !== null;
            $billingAddressData->companyName = $order->getCompanyName();
            $billingAddressData->companyNumber = $order->getCompanyNumber();
            $billingAddressData->companyTaxNumber = $order->getCompanyTaxNumber();
            $billingAddressData->street = $order->getStreet();
            $billingAddressData->city = $order->getCity();
            $billingAddressData->postcode = $order->getPostcode();
            $billingAddressData->country = $order->getCountry();
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
}

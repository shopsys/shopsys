<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\Utils\Utils;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseCustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData as BaseCustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory as BaseCustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Order;

/**
 * @property \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
 * @property \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @method __construct(\App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory, \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory, \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory)
 * @method \App\Model\Customer\User\CustomerUserUpdateData create()
 * @method \App\Model\Customer\DeliveryAddressData getDeliveryAddressDataFromCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\DeliveryAddressData getAmendedDeliveryAddressDataByOrder(\App\Model\Order\Order $order, \App\Model\Customer\DeliveryAddress|null $deliveryAddress = null)
 */
class CustomerUserUpdateDataFactory extends BaseCustomerUserUpdateDataFactory
{
    /**
     * @param \App\Model\Customer\BillingAddressData $billingAddressData
     * @param \App\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     * @return \App\Model\Customer\User\CustomerUserUpdateData
     */
    protected function createInstance(
        BillingAddressData $billingAddressData,
        DeliveryAddressData $deliveryAddressData,
        BaseCustomerUserData $customerUserData,
    ): BaseCustomerUserUpdateData {
        return new CustomerUserUpdateData(
            $billingAddressData,
            $customerUserData,
            $deliveryAddressData,
        );
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): BaseCustomerUserUpdateData
    {
        /** @var \App\Model\Customer\BillingAddress $billingAddress */
        $billingAddress = $customerUser->getCustomer()->getBillingAddress();

        return $this->createInstance(
            $this->billingAddressDataFactory->createFromBillingAddress($billingAddress),
            $this->getDeliveryAddressDataFromCustomerUser($customerUser),
            $this->customerUserDataFactory->createFromCustomerUser($customerUser),
        );
    }

    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \App\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromRegistrationData(RegistrationData $registrationData): BaseCustomerUserUpdateData
    {

        /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
        $billingAddressData = $this->billingAddressDataFactory->create();
        $billingAddressData->city = $registrationData->city;
        $billingAddressData->street = $registrationData->street;
        $billingAddressData->postcode = $registrationData->postcode;
        $billingAddressData->country = $registrationData->country;
        $billingAddressData->companyCustomer = $registrationData->companyCustomer;
        $billingAddressData->companyName = $registrationData->companyName;
        $billingAddressData->companyNumber = $registrationData->companyNumber;
        $billingAddressData->companyTaxNumber = $registrationData->companyTaxNumber;
        $billingAddressData->activated = $registrationData->activated;

        /** @var \App\Model\Customer\User\CustomerUserData $customerUserData */
        $customerUserData = $this->customerUserDataFactory->createForDomainId($registrationData->domainId);
        $customerUserData->createdAt = $registrationData->createdAt;
        $customerUserData->email = $registrationData->email;
        $customerUserData->lastName = $registrationData->lastName;
        $customerUserData->password = $registrationData->password;
        $customerUserData->firstName = $registrationData->firstName;
        $customerUserData->telephone = $registrationData->telephone;
        $customerUserData->newsletterSubscription = $registrationData->newsletterSubscription;

        $customerUserUpdateData = $this->create();
        $customerUserUpdateData->billingAddressData = $billingAddressData;
        $customerUserUpdateData->customerUserData = $customerUserData;
        $customerUserUpdateData->sendRegistrationMail = $registrationData->activated;

        return $customerUserUpdateData;
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \App\Model\Customer\User\CustomerUserUpdateData
     */
    public function createAmendedByOrder(
        CustomerUser $customerUser,
        Order $order,
        ?DeliveryAddress $deliveryAddress,
    ): BaseCustomerUserUpdateData {
        /** @var \App\Model\Customer\BillingAddress $billingAddress */
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
        $customerUserUpdateData->billingAddressData = $this->getAmendedBillingAddressDataByOrder(
            $order,
            $billingAddress,
        );

        /** @var \App\Model\Transport\Transport $transport */
        $transport = $order->getTransport();
        if (!$transport->isPersonalPickup()) {
            $customerUserUpdateData->deliveryAddressData = $this->getAmendedDeliveryAddressDataByOrder(
                $order,
                $deliveryAddress,
            );
        }

        return $customerUserUpdateData;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param \App\Model\Customer\BillingAddress $billingAddress
     * @return \App\Model\Customer\BillingAddressData
     */
    protected function getAmendedBillingAddressDataByOrder(Order $order, BillingAddress $billingAddress)
    {
        $billingAddressData = $this->billingAddressDataFactory->createFromBillingAddress($billingAddress);

        if ($billingAddress->getStreet() === null) {
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
}

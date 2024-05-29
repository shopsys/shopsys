<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseCustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData as BaseCustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory as BaseCustomerUserUpdateDataFactory;

/**
 * @property \App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory
 * @property \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
 * @property \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory
 * @method __construct(\App\Model\Customer\BillingAddressDataFactory $billingAddressDataFactory, \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory, \App\Model\Customer\User\CustomerUserDataFactory $customerUserDataFactory)
 * @method \App\Model\Customer\User\CustomerUserUpdateData create()
 * @method \App\Model\Customer\DeliveryAddressData getDeliveryAddressDataFromCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\DeliveryAddressData getAmendedDeliveryAddressDataByOrder(\App\Model\Order\Order $order, \App\Model\Customer\DeliveryAddress|null $deliveryAddress = null)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createAmendedByOrder(\App\Model\Customer\User\CustomerUser $customerUser, \App\Model\Order\Order $order, \App\Model\Customer\DeliveryAddress|null $deliveryAddress)
 * @method \App\Model\Customer\BillingAddressData getAmendedBillingAddressDataByOrder(\App\Model\Order\Order $order, \App\Model\Customer\BillingAddress $billingAddress)
 * @method \App\Model\Customer\User\CustomerUserUpdateData createFromRegistrationData(\App\Model\Customer\User\RegistrationData $registrationData)
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
        return new CustomerUserUpdateData($billingAddressData, $customerUserData, $deliveryAddressData);
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;

class CustomerUserUpdateData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public $customerUserData;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public $billingAddressData;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData|null
     */
    public $deliveryAddressData;

    /**
     * @var bool
     */
    public $sendRegistrationMail;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     */
    public function __construct(
        BillingAddressData $billingAddressData,
        DeliveryAddressData $deliveryAddressData,
        CustomerUserData $customerUserData
    ) {
        $this->customerUserData = $customerUserData;
        $this->billingAddressData = $billingAddressData;
        $this->deliveryAddressData = $deliveryAddressData;
        $this->sendRegistrationMail = false;
    }
}

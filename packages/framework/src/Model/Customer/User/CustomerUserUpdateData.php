<?php

declare(strict_types=1);

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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData|null $deliveryAddressData
     */
    public function __construct(
        BillingAddressData $billingAddressData,
        CustomerUserData $customerUserData,
        ?DeliveryAddressData $deliveryAddressData,
    ) {
        $this->billingAddressData = $billingAddressData;
        $this->customerUserData = $customerUserData;
        $this->deliveryAddressData = $deliveryAddressData;
        $this->sendRegistrationMail = false;
    }
}

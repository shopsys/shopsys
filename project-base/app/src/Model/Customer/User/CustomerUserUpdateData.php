<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData as BaseUserUpdateData;

/**
 * @property \App\Model\Customer\User\CustomerUserData $customerUserData
 * @property \App\Model\Customer\BillingAddressData $billingAddressData
 * @property \App\Model\Customer\DeliveryAddressData|null $deliveryAddressData
 * @method __construct(\App\Model\Customer\BillingAddressData $billingAddressData, \App\Model\Customer\User\CustomerUserData $customerUserData, \App\Model\Customer\DeliveryAddressData|null $deliveryAddressData)
 */
class CustomerUserUpdateData extends BaseUserUpdateData
{
}

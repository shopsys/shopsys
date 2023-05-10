<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser as BaseCurrentCustomerUser;

/**
 * @property \App\Model\Customer\User\CustomerUser[] $customerUserCache
 * @method \App\Model\Customer\User\CustomerUser|null findCurrentCustomerUser()
 */
class CurrentCustomerUser extends BaseCurrentCustomerUser
{
    public function invalidateCache(): void
    {
        $this->customerUserCache = [];
    }
}

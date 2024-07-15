<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class LoginAdministratorAsUserUrlProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string|null
     */
    public function getSsoLoginAsCustomerUserUrl(CustomerUser $customerUser): ?string
    {
        return null;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class LoginAdministratorAsUserUrlProvider
{
    public function getLoginAsCustomerUserUrl(CustomerUser $customerUser): ?string
    {
        return null;
    }
}

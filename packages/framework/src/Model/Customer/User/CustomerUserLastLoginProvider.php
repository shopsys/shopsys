<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;

class CustomerUserLastLoginProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \DateTime|null
     */
    public function getLastLogin(CustomerUser $customerUser): ?DateTime
    {
        return null;
    }
}

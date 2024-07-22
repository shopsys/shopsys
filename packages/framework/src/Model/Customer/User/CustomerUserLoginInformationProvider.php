<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTime;

class CustomerUserLoginInformationProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \DateTime|null
     */
    public function getLastLogin(CustomerUser $customerUser): ?DateTime
    {
        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string|null
     */
    public function getAdditionalLoginInfo(CustomerUser $customerUser): ?string
    {
        return null;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeImmutable;

class CustomerUserLoginInformationProvider
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \DateTimeImmutable|null
     */
    public function getLastLogin(CustomerUser $customerUser): ?DateTimeImmutable
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

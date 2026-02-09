<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use DateTimeImmutable;

class CustomerUserLoginInformationProvider
{
    public function getLastLogin(CustomerUser $customerUser): ?DateTimeImmutable
    {
        return null;
    }

    public function getAdditionalLoginInfo(CustomerUser $customerUser): ?string
    {
        return null;
    }
}

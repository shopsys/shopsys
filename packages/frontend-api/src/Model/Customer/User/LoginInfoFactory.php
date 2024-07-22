<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginType;

class LoginInfoFactory
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginType $customerUserLoginType
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginInfo
     */
    public function createFromCustomerUserLoginType(CustomerUserLoginType $customerUserLoginType): LoginInfo
    {
        return new LoginInfo(
            $customerUserLoginType->getLoginType(),
            $customerUserLoginType->getExternalId(),
        );
    }
}

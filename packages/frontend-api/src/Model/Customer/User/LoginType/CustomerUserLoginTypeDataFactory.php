<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User\LoginType;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CustomerUserLoginTypeDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $loginType
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData
     */
    public function create(CustomerUser $customerUser, string $loginType): CustomerUserLoginTypeData
    {
        $customerUserLoginTypeData = $this->createInstance();

        $customerUserLoginTypeData->customerUser = $customerUser;
        $customerUserLoginTypeData->loginType = $loginType;

        return $customerUserLoginTypeData;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeData
     */
    protected function createInstance(): CustomerUserLoginTypeData
    {
        return new CustomerUserLoginTypeData();
    }
}

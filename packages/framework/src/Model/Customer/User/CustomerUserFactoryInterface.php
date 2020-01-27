<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

interface CustomerUserFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserData $customerUserData): CustomerUser;
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\Customer\User\CurrentCustomerUserQuery as BaseCurrentCustomerUserQuery;

/**
 * @method __construct(\App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade)
 * @method \App\Model\Customer\User\CustomerUser currentCustomerUserQuery()
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \App\Model\Customer\User\CustomerUser[] customerUsersQuery()
 */
class CurrentCustomerUserQuery extends BaseCurrentCustomerUserQuery
{
    /**
     * @return \App\Model\Customer\User\CustomerUser|null
     */
    public function nullableCurrentCustomerUserQuery(): ?CustomerUser
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        return $customerUser;
    }
}

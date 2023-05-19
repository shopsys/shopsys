<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\User;

use Overblog\GraphQLBundle\Error\UserWarning;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CurrentCustomerUserQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function currentCustomerUserQuery(): CustomerUser
    {
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            throw new UserWarning('No customer user is currently logged in.');
        }

        return $currentCustomerUser;
    }
}

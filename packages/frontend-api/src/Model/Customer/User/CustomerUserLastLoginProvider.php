<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use DateTime;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserLastLoginProvider as BaseCustomerUserLastLoginProvider;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;

class CustomerUserLastLoginProvider extends BaseCustomerUserLastLoginProvider
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     */
    public function __construct(
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getLastLogin(CustomerUser $customerUser): ?DateTime
    {
        return $this->customerUserLoginTypeFacade->getMostRecentLoginType($customerUser)->getLastLoggedInAt();
    }
}

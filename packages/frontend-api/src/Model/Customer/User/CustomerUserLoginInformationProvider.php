<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Customer\User;

use DateTime;
use Override;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserLoginInformationProvider as BaseCustomerUserLoginInformationProvider;
use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade;

class CustomerUserLoginInformationProvider extends BaseCustomerUserLoginInformationProvider
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\CustomerUserLoginTypeFacade $customerUserLoginTypeFacade
     */
    public function __construct(
        protected readonly CustomerUserLoginTypeFacade $customerUserLoginTypeFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getLastLogin(CustomerUser $customerUser): ?DateTime
    {
        return $this->customerUserLoginTypeFacade->findMostRecentLoginType($customerUser)?->getLastLoggedInAt();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAdditionalLoginInfo(CustomerUser $customerUser): ?string
    {
        $loginTypes = $this->customerUserLoginTypeFacade->getAllLoginTypes($customerUser);

        return t('{0}Customer has not logged in yet.|[1,Inf[ Customer uses login via %loginTypes%.', [
            '%count%' => count($loginTypes),
            '%loginTypes%' => implode(', ', $loginTypes),
        ]);
    }
}

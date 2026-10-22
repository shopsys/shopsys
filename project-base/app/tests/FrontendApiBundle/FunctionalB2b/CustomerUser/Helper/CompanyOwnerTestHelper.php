<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\FunctionalB2b\CustomerUser\Helper;

use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;

final class CompanyOwnerTestHelper
{
    public function __construct(
        private readonly CustomerUserDataFactory $customerUserDataFactory,
        private readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function makeCustomerUserAnotherCompanyOwner(CustomerUser $customerUser): void
    {
        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();
        $customerUser->edit($customerUserData);
        $this->em->flush();
    }
}

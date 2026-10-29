<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Test;

use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserDataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;

final class CustomerUserRoleGroupTestHelper
{
    public function __construct(
        private readonly CustomerUserDataFactory $customerUserDataFactory,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function assignRoleGroup(CustomerUser $customerUser, CustomerUserRoleGroup $roleGroupToAssign): void
    {
        $customerUserData = $this->customerUserDataFactory->createFromCustomerUser($customerUser);
        $customerUserData->roleGroup = $roleGroupToAssign;
        $customerUser->edit($customerUserData);
        $this->em->flush();
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Customer\User\Role;

use App\DataFixtures\Demo\CustomerUserRoleGroupDataFixture;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

final class CustomerUserRoleGroupFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CustomerUserRoleGroupFacade $customerUserRoleGroupFacade;

    /**
     * @inject
     */
    private CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory;

    public function testEditedRoleGroupWithAllRolesStoresOnlyAllRoles(): void
    {
        $customerUserRoleGroup = $this->getReference(CustomerUserRoleGroupDataFixture::ROLE_GROUP_USER, CustomerUserRoleGroup::class);
        $customerUserRoleGroupData = $this->customerUserRoleGroupDataFactory->createFromCustomerUserRoleGroup($customerUserRoleGroup);
        $customerUserRoleGroupData->roles = [
            CustomerUserRole::ROLE_API_ALL,
            ...$customerUserRoleGroup->getRoles(),
        ];

        $this->customerUserRoleGroupFacade->edit($customerUserRoleGroup->getId(), $customerUserRoleGroupData);
        $this->em->clear();

        $editedCustomerUserRoleGroup = $this->customerUserRoleGroupFacade->getById($customerUserRoleGroup->getId());
        $this->assertSame([CustomerUserRole::ROLE_API_ALL], $editedCustomerUserRoleGroup->getRoles());
    }
}

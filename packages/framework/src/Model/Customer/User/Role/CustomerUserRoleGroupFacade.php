<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;

class CustomerUserRoleGroupFacade
{
    public function __construct(
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
        protected readonly CustomerUserRoleGroupSetting $customerUserRoleGroupSetting,
        protected readonly CustomerUserRoleGroupDataFactory $customerUserRoleGroupDataFactory,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly CustomerUserRoleGroupFactory $customerUserRoleGroupFactory,
        protected readonly CustomerUserRefreshTokenChainFacade $customerUserRefreshTokenChainFacade,
    ) {
    }

    public function create(CustomerUserRoleGroupData $customerUserRoleGroupData): CustomerUserRoleGroup
    {
        $customerUserRole = $this->customerUserRoleGroupFactory->create($customerUserRoleGroupData);

        $this->entityManager->persist($customerUserRole);
        $this->entityManager->flush();

        return $customerUserRole;
    }

    public function edit(
        int $customerUserRoleGroupId,
        CustomerUserRoleGroupData $administratorRoleGroupData,
    ): CustomerUserRoleGroup {
        $customerUserRoleGroup = $this->customerUserRoleGroupRepository->getById($customerUserRoleGroupId);
        $currentRoles = $customerUserRoleGroup->getRoles();
        $newRoles = $administratorRoleGroupData->roles;

        $customerUserRoleGroup->edit($administratorRoleGroupData);
        $this->entityManager->flush();

        $rolesChanged = ArrayHelper::haveArraysDifferentValues($currentRoles, $newRoles);

        if ($rolesChanged) {
            foreach ($this->customerUserRoleGroupRepository->iterateAllCustomerUsersByRoleGroup($customerUserRoleGroup) as $customerUser) {
                $this->customerUserRefreshTokenChainFacade->removeAllCustomerUserRefreshTokenChains($customerUser);
            }
        }

        return $customerUserRoleGroup;
    }

    public function delete(int $id): void
    {
        $customerUserRoleGroup = $this->customerUserRoleGroupRepository->getById($id);
        $this->entityManager->remove($customerUserRoleGroup);
        $this->entityManager->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->customerUserRoleGroupRepository->getAll();
    }

    public function getDefaultCustomerUserRoleGroup(): CustomerUserRoleGroup
    {
        return $this->customerUserRoleGroupSetting->getDefaultCustomerUserRoleGroup();
    }

    public function getById(int $id): CustomerUserRoleGroup
    {
        return $this->customerUserRoleGroupRepository->getById($id);
    }

    public function getCustomerUserCountByRoleGroup(int $id): int
    {
        return $this->customerUserRoleGroupRepository->getCustomerUserCountByRoleGroup($id);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\Exception\CustomerUserRoleGroupNotFoundException;

class CustomerUserRoleGroupRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerUserRoleGroupRepository()
    {
        return $this->em->getRepository(CustomerUserRoleGroup::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->getCustomerUserRoleGroupRepository()->findAll();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function getById(int $id): CustomerUserRoleGroup
    {
        $roleGroup = $this->getCustomerUserRoleGroupRepository()->findOneBy(['id' => $id]);

        if ($roleGroup === null) {
            throw new CustomerUserRoleGroupNotFoundException('Role group with ID ' . $id . ' not found.');
        }

        return $roleGroup;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function getByUuid(string $uuid): CustomerUserRoleGroup
    {
        $roleGroup = $this->getCustomerUserRoleGroupRepository()->findOneBy(['uuid' => $uuid]);

        if ($roleGroup === null) {
            throw new CustomerUserRoleGroupNotFoundException('Role group with UUID ' . $uuid . ' not found.');
        }

        return $roleGroup;
    }
}

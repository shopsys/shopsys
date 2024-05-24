<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception\DuplicateNameException;

class AdministratorRoleGroupFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupRepository $administratorRoleGroupRepository
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupFactory $administratorRoleGroupFactory
     */
    public function __construct(
        protected readonly AdministratorRoleGroupRepository $administratorRoleGroupRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly AdministratorRoleGroupFactory $administratorRoleGroupFactory,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->administratorRoleGroupRepository->getAllQueryBuilder();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData $roleGroupData
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
    public function create(AdministratorRoleGroupData $roleGroupData): AdministratorRoleGroup
    {
        $administratorRoleGroupByName = $this->administratorRoleGroupRepository->findByName($roleGroupData->name);

        if ($administratorRoleGroupByName !== null) {
            throw new DuplicateNameException($administratorRoleGroupByName->getName());
        }
        $administratorRoleGroup = $this->administratorRoleGroupFactory->create($roleGroupData);

        $this->entityManager->persist($administratorRoleGroup);
        $this->entityManager->flush();

        return $administratorRoleGroup;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
    public function getById(int $id): AdministratorRoleGroup
    {
        return $this->administratorRoleGroupRepository->getById($id);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup $administratorRoleGroup
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
     */
    public function edit(
        AdministratorRoleGroup $administratorRoleGroup,
        AdministratorRoleGroupData $administratorRoleGroupData,
    ): void {
        $this->checkUniqueName($administratorRoleGroup, $administratorRoleGroupData->name);
        $administratorRoleGroup->edit($administratorRoleGroupData);
        $this->entityManager->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup $administratorRoleGroup
     * @param string $name
     */
    protected function checkUniqueName(AdministratorRoleGroup $administratorRoleGroup, string $name): void
    {
        $administratorRoleGroupByName = $this->administratorRoleGroupRepository->findByName($name);

        if ($administratorRoleGroupByName !== null
            && $administratorRoleGroupByName !== $administratorRoleGroup
            && $administratorRoleGroupByName->getName() === $name
        ) {
            throw new DuplicateNameException($administratorRoleGroup->getName());
        }
    }

    /**
     * @param int $id
     */
    public function delete(int $id): void
    {
        $administratorRoleGroup = $this->administratorRoleGroupRepository->getById($id);

        $this->entityManager->remove($administratorRoleGroup);
        $this->entityManager->flush();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->administratorRoleGroupRepository->getAll();
    }
}

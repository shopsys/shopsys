<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception\DuplicateNameException;

class AdministratorRoleGroupFacade
{
    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        protected readonly AdministratorRoleGroupRepository $administratorRoleGroupRepository,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly AdministratorRoleGroupFactory $administratorRoleGroupFactory,
    ) {
    }

    public function getAllNotSystemManagedQueryBuilder(): QueryBuilder
    {
        return $this->administratorRoleGroupRepository->getAllNotSystemManagedQueryBuilder();
    }

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

    public function getById(int $id): AdministratorRoleGroup
    {
        return $this->administratorRoleGroupRepository->getById($id);
    }

    public function edit(
        AdministratorRoleGroup $administratorRoleGroup,
        AdministratorRoleGroupData $administratorRoleGroupData,
    ): void {
        $this->checkUniqueName($administratorRoleGroup, $administratorRoleGroupData->name);
        $administratorRoleGroup->edit($administratorRoleGroupData);
        $this->entityManager->flush();
    }

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

    public function getSystemManagedRoleGroup(string $name): AdministratorRoleGroup
    {
        return $this->administratorRoleGroupRepository->getSystemManagedRoleGroup($name);
    }
}

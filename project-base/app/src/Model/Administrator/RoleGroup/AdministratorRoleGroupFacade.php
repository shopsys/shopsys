<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

use App\Model\Administrator\RoleGroup\Exception\DuplicateNameException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class AdministratorRoleGroupFacade
{
    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupRepository $administratorRoleGroupRepository
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        private AdministratorRoleGroupRepository $administratorRoleGroupRepository,
        private EntityManagerInterface $entityManager,
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
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupData $roleGroupData
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
    public function create(AdministratorRoleGroupData $roleGroupData): AdministratorRoleGroup
    {
        $administratorRoleGroupByName = $this->administratorRoleGroupRepository->findByName($roleGroupData->name);
        if ($administratorRoleGroupByName !== null) {
            throw new DuplicateNameException($administratorRoleGroupByName->getName());
        }
        $administratorRoleGroup = new AdministratorRoleGroup($roleGroupData);

        $this->entityManager->persist($administratorRoleGroup);
        $this->entityManager->flush();

        return $administratorRoleGroup;
    }

    /**
     * @param int $id
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
    public function getById(int $id): AdministratorRoleGroup
    {
        return $this->administratorRoleGroupRepository->getById($id);
    }

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroup $administratorRoleGroup
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupData $administratorRoleGroupData
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
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroup $administratorRoleGroup
     * @param string $name
     */
    private function checkUniqueName(AdministratorRoleGroup $administratorRoleGroup, string $name): void
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
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->administratorRoleGroupRepository->getAll();
    }
}

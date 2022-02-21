<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class AdministratorRoleGroupFacade
{
    /**
     * @var \App\Model\Administrator\RoleGroup\AdministratorRoleGroupRepository
     */
    private AdministratorRoleGroupRepository $administratorRoleGroupRepository;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private EntityManagerInterface $entityManager;

    /**
     * @param \App\Model\Administrator\RoleGroup\AdministratorRoleGroupRepository $administratorRoleGroupRepository
     * @param \Doctrine\ORM\EntityManager $entityManager
     */
    public function __construct(
        AdministratorRoleGroupRepository $administratorRoleGroupRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->administratorRoleGroupRepository = $administratorRoleGroupRepository;
        $this->entityManager = $entityManager;
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
        $administratorRoleGroup = new AdministratorRoleGroup($roleGroupData);

        $this->entityManager->persist($administratorRoleGroup);
        $this->entityManager->flush();

        return $administratorRoleGroup;
    }
}

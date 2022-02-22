<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

use App\Model\Administrator\RoleGroup\Exception\AdministratorRoleGroupNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class AdministratorRoleGroupRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getAdministratorRoleGroupRepository()
    {
        return $this->entityManager->getRepository(AdministratorRoleGroup::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getAdministratorRoleGroupRepository()->createQueryBuilder('arg');
    }

    /**
     * @param int $id
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
    public function getById(int $id): AdministratorRoleGroup
    {
        $administratorRoleGroup = $this->getAdministratorRoleGroupRepository()->find($id);

        if ($administratorRoleGroup === null) {
            throw new AdministratorRoleGroupNotFoundException('Administrator role group with id `' . $id . '` not found.');
        }

        return $administratorRoleGroup;
    }

    /**
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->getAllQueryBuilder()->getQuery()->getResult();
    }
}

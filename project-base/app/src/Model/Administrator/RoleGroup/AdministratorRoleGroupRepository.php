<?php

declare(strict_types=1);

namespace App\Model\Administrator\RoleGroup;

use App\Model\Administrator\RoleGroup\Exception\AdministratorRoleGroupNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class AdministratorRoleGroupRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getAdministratorRoleGroupRepository(): EntityRepository
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

    /**
     * @param string $name
     * @return \App\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    public function findByName(string $name): ?AdministratorRoleGroup
    {
        return $this->getAdministratorRoleGroupRepository()->findOneBy(['name' => $name]);
    }
}

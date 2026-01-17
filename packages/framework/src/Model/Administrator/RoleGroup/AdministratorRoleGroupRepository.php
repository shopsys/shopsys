<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception\AdministratorRoleGroupNotFoundException;

class AdministratorRoleGroupRepository
{
    public function __construct(protected EntityManagerInterface $entityManager)
    {
    }

    protected function getAdministratorRoleGroupRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(AdministratorRoleGroup::class);
    }

    public function getAllNotSystemManagedQueryBuilder(): QueryBuilder
    {
        return $this->getAdministratorRoleGroupRepository()->createQueryBuilder('arg')
            ->where('arg.systemManaged = :systemManaged')
            ->setParameter('systemManaged', false);
    }

    public function getById(int $id): AdministratorRoleGroup
    {
        $administratorRoleGroup = $this->getAllNotSystemManagedQueryBuilder()
            ->andWhere('arg.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if ($administratorRoleGroup === null) {
            throw new AdministratorRoleGroupNotFoundException('Administrator role group with id `' . $id . '` not found.');
        }

        return $administratorRoleGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->getAdministratorRoleGroupRepository()->findAll();
    }

    public function findByName(string $name): ?AdministratorRoleGroup
    {
        return $this->getAdministratorRoleGroupRepository()->findOneBy(['name' => $name]);
    }

    public function getSystemManagedRoleGroup(string $name): AdministratorRoleGroup
    {
        return $this->getAdministratorRoleGroupRepository()
            ->createQueryBuilder('arg')
            ->where('arg.systemManaged = :systemManaged')
            ->andWhere('arg.name = :name')
            ->setParameter('systemManaged', true)
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

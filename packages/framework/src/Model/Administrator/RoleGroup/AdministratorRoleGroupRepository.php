<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception\AdministratorRoleGroupNotFoundException;

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
    protected function getAdministratorRoleGroupRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(AdministratorRoleGroup::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllNotSystemManagedQueryBuilder(): QueryBuilder
    {
        return $this->getAdministratorRoleGroupRepository()->createQueryBuilder('arg')
            ->where('arg.systemManaged = :systemManaged')
            ->setParameter('systemManaged', false);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
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

    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup|null
     */
    public function findByName(string $name): ?AdministratorRoleGroup
    {
        return $this->getAdministratorRoleGroupRepository()->findOneBy(['name' => $name]);
    }

    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\AdministratorRoleGroup
     */
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

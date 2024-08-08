<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
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

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getCustomerUserRoleGroupRepository()->createQueryBuilder('cug');
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilderByLocale(string $locale): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $locale
     */
    protected function addTranslation(QueryBuilder $queryBuilder, string $locale): void
    {
        $queryBuilder
            ->addSelect('cugt')
            ->join('cug.translations', 'cugt', Join::WITH, 'cugt.locale = :locale')
            ->setParameter('locale', $locale);
    }

    /**
     * @param int $id
     * @return int
     */
    public function getCustomerUserCountByRoleGroup(int $id): int
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('COUNT(cu.id)')
            ->from(CustomerUser::class, 'cu')
            ->where('cu.roleGroup = :roleGroup')
            ->setParameter('roleGroup', $id);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }
}

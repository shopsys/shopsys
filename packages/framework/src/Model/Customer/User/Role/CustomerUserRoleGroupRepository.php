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

    public function getById(int $id): CustomerUserRoleGroup
    {
        $roleGroup = $this->getCustomerUserRoleGroupRepository()->findOneBy(['id' => $id]);

        if ($roleGroup === null) {
            throw new CustomerUserRoleGroupNotFoundException('Role group with ID ' . $id . ' not found.');
        }

        return $roleGroup;
    }

    public function getByUuid(string $uuid): CustomerUserRoleGroup
    {
        $roleGroup = $this->getCustomerUserRoleGroupRepository()->findOneBy(['uuid' => $uuid]);

        if ($roleGroup === null) {
            throw new CustomerUserRoleGroupNotFoundException('Role group with UUID ' . $uuid . ' not found.');
        }

        return $roleGroup;
    }

    protected function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getCustomerUserRoleGroupRepository()->createQueryBuilder('cug');
    }

    public function getAllQueryBuilderByLocale(string $locale): QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder();
        $this->addTranslation($queryBuilder, $locale);

        return $queryBuilder;
    }

    protected function addTranslation(QueryBuilder $queryBuilder, string $locale): void
    {
        $queryBuilder
            ->addSelect('cugt')
            ->join('cug.translations', 'cugt', Join::WITH, 'cugt.locale = :locale')
            ->setParameter('locale', $locale);
    }

    public function getCustomerUserCountByRoleGroup(int $id): int
    {
        $queryBuilder = $this->getCustomerUsersByRoleGroupIdQueryBuilder($id);
        $queryBuilder->select('COUNT(cu)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return iterable<int, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser>
     */
    public function iterateAllCustomerUsersByRoleGroup(CustomerUserRoleGroup $customerUserRoleGroup): iterable
    {
        return $this->getCustomerUsersByRoleGroupIdQueryBuilder($customerUserRoleGroup->getId())
            ->getQuery()
            ->toIterable();
    }

    protected function getCustomerUsersByRoleGroupIdQueryBuilder(int $customerUserRoleGroupId): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder
            ->select('cu')
            ->from(CustomerUser::class, 'cu')
            ->where('cu.roleGroup = :roleGroup')
            ->setParameter('roleGroup', $customerUserRoleGroupId);

        return $queryBuilder;
    }
}

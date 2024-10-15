<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup;

class CustomerRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCustomerRepository(): EntityRepository
    {
        return $this->em->getRepository(Customer::class);
    }

    /**
     * @param int $customerId
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getById(int $customerId): Customer
    {
        $customer = $this->getCustomerRepository()->find($customerId);

        if ($customer === null) {
            throw new CustomerNotFoundException('Customer with ID ' . $customerId . ' not found.');
        }

        return $customer;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return bool
     */
    public function isWithoutCustomerUsers(Customer $customer): bool
    {
        return $this->getCustomerUsersCount($customer) === 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return int
     */
    protected function getCustomerUsersCount(Customer $customer): int
    {
        return $this->getCustomerUsersQueryBuilder($customer)
            ->select('count(cu.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser[]
     */
    public function getCustomerUsers(Customer $customer): array
    {
        return
            $this->getCustomerUsersQueryBuilder($customer)
            ->select('cu')
            ->addOrderBy('cu.lastName, cu.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getCustomerUsersQueryBuilder(Customer $customer): QueryBuilder
    {
        return $this->em
            ->createQueryBuilder()
            ->from(Customer::class, 'c')
            ->join(CustomerUser::class, 'cu', Join::WITH, 'cu.customer = c')
            ->where('c = :customer')
            ->setParameter('customer', $customer);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup $customerUserRoleGroup
     * @return int
     */
    public function getCountOfCustomerUsersByCustomerUserRoleGroup(
        Customer $customer,
        CustomerUserRoleGroup $customerUserRoleGroup,
    ): int {
        return (int)$this->em
            ->createQueryBuilder()
            ->select('count(cu.id)')
            ->from(Customer::class, 'c')
            ->join(CustomerUser::class, 'cu', Join::WITH, 'cu.customer = c')
            ->where('c = :customer')
            ->andWhere('cu.roleGroup = :roleGroup')
            ->setParameter('customer', $customer)
            ->setParameter('roleGroup', $customerUserRoleGroup)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

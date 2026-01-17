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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getCustomerRepository(): EntityRepository
    {
        return $this->em->getRepository(Customer::class);
    }

    public function getById(int $customerId): Customer
    {
        $customer = $this->getCustomerRepository()->find($customerId);

        if ($customer === null) {
            throw new CustomerNotFoundException('Customer with ID ' . $customerId . ' not found.');
        }

        return $customer;
    }

    public function isWithoutCustomerUsers(Customer $customer): bool
    {
        return $this->getCustomerUsersCount($customer) === 0;
    }

    protected function getCustomerUsersCount(Customer $customer): int
    {
        return $this->getCustomerUsersQueryBuilder($customer)
            ->select('count(cu.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
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

    protected function getCustomerUsersQueryBuilder(Customer $customer): QueryBuilder
    {
        return $this->em
            ->createQueryBuilder()
            ->from(Customer::class, 'c')
            ->join(CustomerUser::class, 'cu', Join::WITH, 'cu.customer = c')
            ->where('c = :customer')
            ->setParameter('customer', $customer);
    }

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

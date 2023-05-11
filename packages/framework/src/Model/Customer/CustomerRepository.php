<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerNotFoundException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

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
        $count = (int)$this->em
            ->createQueryBuilder()
            ->select('count(c.id)')
            ->from(Customer::class, 'c')
            ->join(CustomerUser::class, 'cu', Join::WITH, 'cu.customer = c')
            ->where('c = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getSingleScalarResult();

        return $count === 0;
    }
}

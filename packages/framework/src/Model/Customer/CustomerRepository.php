<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class CustomerRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

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
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\CustomerNotFoundException('Customer with ID ' . $customerId . ' not found.');
        }

        return $customer;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;

class CustomerFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface
     */
    protected $customerFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface $customerFactory
     */
    public function __construct(EntityManagerInterface $em, CustomerFactoryInterface $customerFactory)
    {
        $this->em = $em;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function createCustomer(): Customer
    {
        return $this->customerFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function createCustomerWithBillingAddress(Customer $customer, BillingAddress $billingAddress): Customer
    {
        $customer->addBillingAddress($billingAddress);
        $this->em->persist($customer);
        $this->em->flush($customer);

        return $customer;
    }
}

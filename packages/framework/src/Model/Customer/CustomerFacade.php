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
     * @var \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface
     */
    protected $billingAddressFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface $customerFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressFactoryInterface $billingAddressFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        CustomerFactoryInterface $customerFactory,
        BillingAddressFactoryInterface $billingAddressFactory
    ) {
        $this->em = $em;
        $this->customerFactory = $customerFactory;
        $this->billingAddressFactory = $billingAddressFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function createCustomer(): Customer
    {
        return $this->customerFactory->create();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function createCustomerWithBillingAddress(BillingAddressData $billingAddressData): Customer
    {
        $customer = $this->createCustomer();
        $billingAddressData->customer = $customer;

        $customer->addBillingAddress($this->billingAddressFactory->create($billingAddressData));
        $this->em->persist($customer);
        $this->em->flush($customer);

        return $customer;
    }
}

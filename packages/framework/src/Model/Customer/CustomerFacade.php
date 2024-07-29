<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupSetting;

class CustomerFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFactoryInterface $customerFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerRepository $customerRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupSetting $customerUserRoleGroupSetting
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerFactoryInterface $customerFactory,
        protected readonly CustomerRepository $customerRepository,
        protected readonly Domain $domain,
        protected readonly CustomerUserRoleGroupSetting $customerUserRoleGroupSetting,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerData $customerData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function create(CustomerData $customerData): Customer
    {
        $customer = $this->customerFactory->create($customerData);
        $this->em->persist($customer);
        $this->em->flush();

        return $customer;
    }

    /**
     * @param int $customerId
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerData $customerData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function edit(int $customerId, CustomerData $customerData): Customer
    {
        $customer = $this->customerRepository->getById($customerId);
        $customer->edit($customerData);
        $this->em->flush();

        return $customer;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     */
    public function deleteIfNoCustomerUsersLeft(Customer $customer): void
    {
        if ($this->customerRepository->isWithoutCustomerUsers($customer)) {
            $this->delete($customer);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     */
    public function deleteAll(Customer $customer): void
    {
        foreach ($this->getCustomerUsers($customer) as $customerUser) {
            $this->em->remove($customerUser);
        }

        $this->em->flush();
        $this->delete($customer);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     */
    protected function delete(Customer $customer): void
    {
        $this->em->remove($customer);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return bool
     */
    public function isB2bFeaturesEnabledByCustomer(Customer $customer): bool
    {
        $domainConfig = $this->domain->getDomainConfigById($customer->getDomainId());

        return $domainConfig->isB2b() && $customer->isCompanyCustomer();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser[]
     */
    public function getCustomerUsers(Customer $customer): array
    {
        return $this->customerRepository->getCustomerUsers($customer);
    }

    /**
     * @param int $customerId
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getById(int $customerId): Customer
    {
        return $this->customerRepository->getById($customerId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return bool
     */
    public function hasMultipleCustomerUsersWithDefaultCustomerUserRoleGroup(Customer $customer): bool
    {
        $defaultCustomerUserRoleGroup = $this->customerUserRoleGroupSetting->getDefaultCustomerUserRoleGroup();

        return $this->customerRepository->getCountOfCustomerUsersByCustomerUserRoleGroup($customer, $defaultCustomerUserRoleGroup) > 1;
    }
}

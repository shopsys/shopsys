<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class CustomerUserDataFactory
{
    public function __construct(
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        protected readonly CustomerRepository $customerRepository,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly ClockInterface $clock,
    ) {
    }

    protected function createInstance(): CustomerUserData
    {
        return new CustomerUserData();
    }

    public function create(): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->createdAt = $this->clock->now();
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();

        return $customerUserData;
    }

    public function createForCustomer(Customer $customer): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->createdAt = $this->clock->now();
        $customerUserData->customer = $customer;
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();

        return $customerUserData;
    }

    public function createForCustomerWithPresetPricingGroup(Customer $customer): CustomerUserData
    {
        $customerUserData = $this->createForCustomer($customer);
        $customerUsers = $this->customerRepository->getCustomerUsers($customer);
        $customerUser = array_first($customerUsers);
        $customerUserData->pricingGroup = $customerUser->getPricingGroup();
        $customerUserData->domainId = $customerUser->getDomainId();

        return $customerUserData;
    }

    public function createForDomainId(int $domainId): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->createdAt = $this->clock->now();
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();
        $this->fillForDomainId($customerUserData, $domainId);

        return $customerUserData;
    }

    protected function fillForDomainId(CustomerUserData $customerUserData, int $domainId): void
    {
        $customerUserData->pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(
            $domainId,
        );
        $customerUserData->domainId = $domainId;
    }

    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $this->fillFromUser($customerUserData, $customerUser);

        return $customerUserData;
    }

    protected function fillFromUser(CustomerUserData $customerUserData, CustomerUser $customerUser): void
    {
        $customerUserData->domainId = $customerUser->getDomainId();
        $customerUserData->firstName = $customerUser->getFirstName();
        $customerUserData->lastName = $customerUser->getLastName();
        $customerUserData->email = $customerUser->getEmail();
        $customerUserData->pricingGroup = $customerUser->getPricingGroup();
        $customerUserData->salesRepresentative = $customerUser->getSalesRepresentative();
        $customerUserData->createdAt = $customerUser->getCreatedAt();
        $customerUserData->telephone = $customerUser->getTelephoneData();
        $customerUserData->customer = $customerUser->getCustomer();
        $customerUserData->defaultDeliveryAddress = $customerUser->getDefaultDeliveryAddress();
        $customerUserData->newsletterSubscription = $this->newsletterFacade->isSubscribed($customerUser);
        $customerUserData->roleGroup = $customerUser->getRoleGroup();
    }
}

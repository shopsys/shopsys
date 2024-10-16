<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerRepository;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class CustomerUserDataFactory implements CustomerUserDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade $customerUserRoleGroupFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerRepository $customerRepository
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        protected readonly CustomerRepository $customerRepository,
        protected readonly NewsletterFacade $newsletterFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    protected function createInstance(): CustomerUserData
    {
        return new CustomerUserData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function create(): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomer(Customer $customer): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->customer = $customer;
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomerWithPresetPricingGroup(Customer $customer): CustomerUserData
    {
        $customerUserData = $this->createForCustomer($customer);
        $customerUsers = $this->customerRepository->getCustomerUsers($customer);
        $customerUser = reset($customerUsers);
        $customerUserData->pricingGroup = $customerUser->getPricingGroup();
        $customerUserData->domainId = $customerUser->getDomainId();

        return $customerUserData;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForDomainId(int $domainId): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->roleGroup = $this->customerUserRoleGroupFacade->getDefaultCustomerUserRoleGroup();
        $this->fillForDomainId($customerUserData, $domainId);

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param int $domainId
     */
    protected function fillForDomainId(CustomerUserData $customerUserData, int $domainId)
    {
        $customerUserData->pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(
            $domainId,
        );
        $customerUserData->domainId = $domainId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $this->fillFromUser($customerUserData, $customerUser);

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    protected function fillFromUser(CustomerUserData $customerUserData, CustomerUser $customerUser)
    {
        $customerUserData->domainId = $customerUser->getDomainId();
        $customerUserData->firstName = $customerUser->getFirstName();
        $customerUserData->lastName = $customerUser->getLastName();
        $customerUserData->email = $customerUser->getEmail();
        $customerUserData->pricingGroup = $customerUser->getPricingGroup();
        $customerUserData->salesRepresentative = $customerUser->getSalesRepresentative();
        $customerUserData->createdAt = $customerUser->getCreatedAt();
        $customerUserData->telephone = $customerUser->getTelephone();
        $customerUserData->customer = $customerUser->getCustomer();
        $customerUserData->defaultDeliveryAddress = $customerUser->getDefaultDeliveryAddress();
        $customerUserData->newsletterSubscription = $this->newsletterFacade->isSubscribed($customerUser);
        $customerUserData->roleGroup = $customerUser->getRoleGroup();
    }
}

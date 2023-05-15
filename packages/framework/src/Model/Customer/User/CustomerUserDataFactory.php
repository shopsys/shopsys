<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class CustomerUserDataFactory implements CustomerUserDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade)
    {
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
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomer(Customer $customer): CustomerUserData
    {
        $customerUserData = $this->createInstance();
        $customerUserData->customer = $customer;

        return $customerUserData;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForDomainId(int $domainId): CustomerUserData
    {
        $customerUserData = $this->createInstance();
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
        $customerUserData->createdAt = $customerUser->getCreatedAt();
        $customerUserData->telephone = $customerUser->getTelephone();
        $customerUserData->customer = $customerUser->getCustomer();
        $customerUserData->defaultDeliveryAddress = $customerUser->getDefaultDeliveryAddress();
    }
}

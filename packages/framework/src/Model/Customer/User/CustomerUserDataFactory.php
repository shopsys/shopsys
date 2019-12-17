<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class CustomerUserDataFactory implements CustomerUserDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(PricingGroupSettingFacade $pricingGroupSettingFacade)
    {
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function create(): CustomerUserData
    {
        return new CustomerUserData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomer(Customer $customer): CustomerUserData
    {
        $customerUserData = $this->create();
        $customerUserData->customer = $customer;
        return $customerUserData;
    }

    /**
     * @param int $domainId
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForDomainId(int $domainId): CustomerUserData
    {
        $customerUserData = $this->create();
        $this->fillForDomainId($customerUserData, $domainId);

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param int $domainId
     */
    protected function fillForDomainId(CustomerUserData $customerUserData, int $domainId)
    {
        $customerUserData->pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        $customerUserData->domainId = $domainId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserData
    {
        $customerUserData = $this->create();
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
    }
}

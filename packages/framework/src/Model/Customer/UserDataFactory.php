<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class UserDataFactory implements UserDataFactoryInterface
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
     * @return \Shopsys\FrameworkBundle\Model\Customer\UserData
     */
    public function create(): UserData
    {
        return new UserData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\UserData
     */
    public function createForCustomer(Customer $customer): UserData
    {
        $userData = $this->create();
        $userData->customer = $customer;
        return $userData;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\UserData
     */
    public function createForDomainId(int $domainId): UserData
    {
        $userData = $this->create();
        $this->fillForDomainId($userData, $domainId);

        return $userData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @param int $domainId
     */
    protected function fillForDomainId(UserData $userData, int $domainId)
    {
        $userData->pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
        $userData->domainId = $domainId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @return \Shopsys\FrameworkBundle\Model\Customer\UserData
     */
    public function createFromUser(User $user): UserData
    {
        $userData = $this->create();
        $this->fillFromUser($userData, $user);

        return $userData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     */
    protected function fillFromUser(UserData $userData, User $user)
    {
        $userData->domainId = $user->getDomainId();
        $userData->firstName = $user->getFirstName();
        $userData->lastName = $user->getLastName();
        $userData->email = $user->getEmail();
        $userData->pricingGroup = $user->getPricingGroup();
        $userData->createdAt = $user->getCreatedAt();
        $userData->telephone = $user->getTelephone();
        $userData->customer = $user->getCustomer();
    }
}

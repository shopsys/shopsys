<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User as BaseUser;
use Shopsys\FrameworkBundle\Model\Customer\UserData as BaseUserData;
use Shopsys\FrameworkBundle\Model\Customer\UserDataFactory as BaseUserDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class UserDataFactory extends BaseUserDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(PricingGroupSettingFacade $pricingGroupSettingFacade)
    {
        parent::__construct($pricingGroupSettingFacade);
    }

    /**
     * @return \App\Model\Customer\UserData
     */
    public function create(): BaseUserData
    {
        return new UserData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \App\Model\Customer\UserData
     */
    public function createForCustomer(Customer $customer): BaseUserData
    {
        $userData = $this->create();
        $userData->customer = $customer;
        return $userData;
    }

    /**
     * @param int $domainId
     * @return \App\Model\Customer\UserData
     */
    public function createForDomainId(int $domainId): BaseUserData // @todo -> createForDomainIdAndCustomer
    {
        $userData = $this->create();
        $this->fillForDomainId($userData, $domainId);

        return $userData;
    }

    /**
     * @param \App\Model\Customer\User $user
     * @return \App\Model\Customer\UserData
     */
    public function createFromUser(BaseUser $user): BaseUserData
    {
        $userData = $this->create();
        $this->fillFromUser($userData, $user);

        return $userData;
    }
}

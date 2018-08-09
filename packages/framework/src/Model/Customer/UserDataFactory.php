<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;

class UserDataFactory implements UserDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    public function __construct(PricingGroupSettingFacade $pricingGroupSettingFacade)
    {
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
    }

    public function create(): UserData
    {
        return new UserData();
    }

    public function createForDomainId(int $domainId): UserData
    {
        $userData = new UserData();
        $this->fillForDomainId($userData, $domainId);

        return $userData;
    }

    protected function fillForDomainId(UserData $userData, int $domainId)
    {
        $userData->pricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);
    }

    public function createFromUser(User $user): UserData
    {
        $userData = new UserData();
        $this->fillFromUser($userData, $user);

        return $userData;
    }

    private function fillFromUser(UserData $userData, User $user)
    {
        $userData->domainId = $user->getDomainId();
        $userData->firstName = $user->getFirstName();
        $userData->lastName = $user->getLastName();
        $userData->email = $user->getEmail();
        $userData->pricingGroup = $user->getPricingGroup();
        $userData->createdAt = $user->getCreatedAt();
    }
}

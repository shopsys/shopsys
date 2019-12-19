<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentCustomerUser
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PricingGroupSettingFacade $pricingGroupSettingFacade
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        $customerUser = $this->findCurrentCustomerUser();
        if ($customerUser === null) {
            return $this->pricingGroupSettingFacade->getDefaultPricingGroupByCurrentDomain();
        } else {
            return $customerUser->getPricingGroup();
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function findCurrentCustomerUser()
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }

        $customerUser = $token->getUser();
        if (!$customerUser instanceof CustomerUser) {
            return null;
        }

        return $customerUser;
    }
}

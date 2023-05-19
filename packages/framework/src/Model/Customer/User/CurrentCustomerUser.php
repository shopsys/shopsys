<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentCustomerUser
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser[]
     */
    protected array $customerUserCache = [];

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    protected $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     */
    protected $customerUserFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        CustomerUserFacade $customerUserFacade
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->customerUserFacade = $customerUserFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        $customerUser = $this->findCurrentCustomerUser();

        if ($customerUser === null) {
            return $this->pricingGroupSettingFacade->getDefaultPricingGroupByCurrentDomain();
        }

        return $customerUser->getPricingGroup();
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

        if (array_key_exists($token->getUserIdentifier(), $this->customerUserCache) === true) {
            return $this->customerUserCache[$token->getUserIdentifier()];
        }

        $user = $token->getUser();

        if (
            class_exists('\Shopsys\FrontendApiBundle\Model\User\FrontendApiUser')
            && $user instanceof FrontendApiUser
        ) {
            $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());
            $this->customerUserCache[$token->getUserIdentifier()] = $customerUser;

            return $customerUser;
        }

        if ($user instanceof CustomerUser) {
            $this->customerUserCache[$token->getUserIdentifier()] = $user;

            return $user;
        }

        return null;
    }
}

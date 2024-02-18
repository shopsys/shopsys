<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\TwoLevelCache\TwoLevelCacheProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentCustomerUser
{
    protected const CURRENT_CUSTOMER_USER_CACHE_KEY = 'currentCustomerUser';

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\TwoLevelCache\TwoLevelCacheProvider $twoLevelCacheProvider
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly TwoLevelCacheProvider $twoLevelCacheProvider,
    ) {
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

        if ($this->twoLevelCacheProvider->has(static::CURRENT_CUSTOMER_USER_CACHE_KEY, $token->getUserIdentifier())) {
            return $this->twoLevelCacheProvider->get(static::CURRENT_CUSTOMER_USER_CACHE_KEY, $token->getUserIdentifier());
        }

        $user = $token->getUser();

        if (
            class_exists('\Shopsys\FrontendApiBundle\Model\User\FrontendApiUser')
            && $user instanceof FrontendApiUser
        ) {
            $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());
            $this->twoLevelCacheProvider->add(static::CURRENT_CUSTOMER_USER_CACHE_KEY, $token->getUserIdentifier(), $customerUser);

            return $customerUser;
        }

        if ($user instanceof CustomerUser) {
            $this->twoLevelCacheProvider->add(static::CURRENT_CUSTOMER_USER_CACHE_KEY, $token->getUserIdentifier(), $user);

            return $user;
        }

        return null;
    }
}

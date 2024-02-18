<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentCustomerUser
{
    protected const CURRENT_CUSTOMER_USER_CACHE_NAMESPACE = 'currentCustomerUser';

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly InMemoryCache $inMemoryCache,
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

        if ($this->inMemoryCache->hasItem(static::CURRENT_CUSTOMER_USER_CACHE_NAMESPACE, $token->getUserIdentifier())) {
            return $this->inMemoryCache->getItem(static::CURRENT_CUSTOMER_USER_CACHE_NAMESPACE, $token->getUserIdentifier());
        }

        $user = $token->getUser();

        if (
            class_exists('\Shopsys\FrontendApiBundle\Model\User\FrontendApiUser')
            && $user instanceof FrontendApiUser
        ) {
            $customerUser = $this->customerUserFacade->getByUuid($user->getUuid());
            $this->inMemoryCache->save(static::CURRENT_CUSTOMER_USER_CACHE_NAMESPACE, $customerUser, $token->getUserIdentifier());

            return $customerUser;
        }

        if ($user instanceof CustomerUser) {
            $this->inMemoryCache->save(static::CURRENT_CUSTOMER_USER_CACHE_NAMESPACE, $user, $token->getUserIdentifier());

            return $user;
        }

        return null;
    }
}

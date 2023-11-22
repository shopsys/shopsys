<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Service\ResetInterface;

class CurrentCustomerUser implements ResetInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser[]
     */
    protected array $customerUserCache = [];

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly CustomerUserFacade $customerUserFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
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
    public function findCurrentCustomerUser(): ?\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
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

    public function reset(): void
    {
        $this->customerUserCache = [];
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerUserIdentifierFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    public function get(): CustomerUserIdentifier
    {
        try {
            $cartIdentifier = $this->requestStack->getSession()->getId();
        } catch (SessionNotFoundException) {
            $cartIdentifier = '';
        }

        // when session is not started, returning empty string is behavior of session_id()
        if ($cartIdentifier === '') {
            $this->requestStack->getSession()->start();
            $cartIdentifier = $this->requestStack->getSession()->getId();
        }

        return new CustomerUserIdentifier($cartIdentifier, $this->currentCustomerUser->findCurrentCustomerUser());
    }

    /**
     * @param string $cartIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    public function getOnlyWithCartIdentifier(string $cartIdentifier): CustomerUserIdentifier
    {
        return new CustomerUserIdentifier($cartIdentifier, null);
    }
}

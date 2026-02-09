<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerUserIdentifierFactory
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly RequestStack $requestStack,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

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

    public function getOnlyWithCartIdentifier(?string $cartIdentifier): CustomerUserIdentifier
    {
        if ($this->transformStringHelper->emptyToNull($cartIdentifier) === null) {
            $cartIdentifier = Uuid::uuid4()->toString();
        }

        return new CustomerUserIdentifier($cartIdentifier);
    }

    public function getByCustomerUser(CustomerUser $customerUser): CustomerUserIdentifier
    {
        return new CustomerUserIdentifier('', $customerUser);
    }
}

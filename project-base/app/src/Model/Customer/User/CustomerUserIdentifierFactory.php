<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory as BaseCustomerUserIdentifierFactory;

/**
 * @method __construct(\App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Symfony\Component\HttpFoundation\RequestStack $requestStack)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class CustomerUserIdentifierFactory extends BaseCustomerUserIdentifierFactory
{
    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    public function getByCustomerUser(CustomerUser $customerUser): CustomerUserIdentifier
    {
        return new CustomerUserIdentifier('', $customerUser);
    }

    /**
     * @param string|null $cartIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    public function getByCartIdentifier(?string $cartIdentifier): CustomerUserIdentifier
    {
        if (TransformString::emptyToNull($cartIdentifier) === null) {
            $cartIdentifier = Uuid::uuid4()->toString();
        }

        return new CustomerUserIdentifier($cartIdentifier);
    }
}

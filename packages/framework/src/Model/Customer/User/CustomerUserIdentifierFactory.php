<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

class CustomerUserIdentifierFactory
{
    public function __construct(
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
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

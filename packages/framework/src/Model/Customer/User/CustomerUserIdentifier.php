<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerUserIdentifierException;

class CustomerUserIdentifier
{
    protected string $cartIdentifier = '';

    public function __construct(string $cartIdentifier, protected readonly ?CustomerUser $customerUser = null)
    {
        if ($cartIdentifier === '' && $customerUser === null) {
            $message = 'Can not be created empty CustomerUserIdentifier';

            throw new EmptyCustomerUserIdentifierException($message);
        }

        if ($this->customerUser === null) {
            $this->cartIdentifier = $cartIdentifier;
        }
    }

    public function getCartIdentifier(): string
    {
        return $this->cartIdentifier;
    }

    public function getCustomerUser(): ?CustomerUser
    {
        return $this->customerUser;
    }
}

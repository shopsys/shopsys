<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerUserIdentifierException;

class CustomerUserIdentifier
{
    protected string $cartIdentifier = '';

    /**
     * @param string $cartIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function __construct($cartIdentifier, protected readonly ?CustomerUser $customerUser = null)
    {
        if ($cartIdentifier === '' && $customerUser === null) {
            $message = 'Can not be created empty CustomerUserIdentifier';

            throw new EmptyCustomerUserIdentifierException($message);
        }

        if ($this->customerUser === null) {
            $this->cartIdentifier = $cartIdentifier;
        }
    }

    /**
     * @return string
     */
    public function getCartIdentifier(): string
    {
        return $this->cartIdentifier;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null
     */
    public function getCustomerUser(): ?\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
    {
        return $this->customerUser;
    }

    /**
     * @return string
     */
    public function getObjectHash(): string
    {
        if ($this->customerUser instanceof CustomerUser) {
            $customerUserId = $this->customerUser->getId();
        } else {
            $customerUserId = 'NULL';
        }

        return 'session:' . $this->cartIdentifier . ';userId:' . $customerUserId . ';';
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class CustomerIdentifier
{
    /**
     * @var string
     */
    private $cartIdentifier = '';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User|null
     */
    private $user;

    public function __construct(string $cartIdentifier, User $user = null)
    {
        if ($cartIdentifier === '' && $user === null) {
            $message = 'Can not be created empty CustomerIdentifier';
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerIdentifierException($message);
        }

        $this->user = $user;
        if ($this->user === null) {
            $this->cartIdentifier = $cartIdentifier;
        }
    }

    public function getCartIdentifier(): string
    {
        return $this->cartIdentifier;
    }

    public function getUser(): ?\Shopsys\FrameworkBundle\Model\Customer\User
    {
        return $this->user;
    }

    public function getObjectHash(): string
    {
        if ($this->user instanceof User) {
            $userId = $this->user->getId();
        } else {
            $userId = 'NULL';
        }
        return 'session:' . $this->cartIdentifier . ';userId:' . $userId . ';';
    }
}

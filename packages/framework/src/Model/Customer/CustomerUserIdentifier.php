<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class CustomerUserIdentifier
{
    /**
     * @var string
     */
    protected $cartIdentifier = '';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerUser|null
     */
    protected $customerUser;

    /**
     * @param string $cartIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUser|null $customerUser
     */
    public function __construct($cartIdentifier, ?CustomerUser $customerUser = null)
    {
        if ($cartIdentifier === '' && $customerUser === null) {
            $message = 'Can not be created empty CustomerUserIdentifier';
            throw new \Shopsys\FrameworkBundle\Model\Customer\Exception\EmptyCustomerIdentifierException($message);
        }

        $this->customerUser = $customerUser;
        if ($this->customerUser === null) {
            $this->cartIdentifier = $cartIdentifier;
        }
    }

    /**
     * @return string
     */
    public function getCartIdentifier()
    {
        return $this->cartIdentifier;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUser|null
     */
    public function getCustomerUser()
    {
        return $this->customerUser;
    }

    /**
     * @return string
     */
    public function getObjectHash()
    {
        if ($this->customerUser instanceof CustomerUser) {
            $customerUserId = $this->customerUser->getId();
        } else {
            $customerUserId = 'NULL';
        }
        return 'session:' . $this->cartIdentifier . ';userId:' . $customerUserId . ';';
    }
}

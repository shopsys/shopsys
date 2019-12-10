<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomerUserIdentifierFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(CurrentCustomerUser $currentCustomerUser, SessionInterface $session)
    {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->session = $session;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserIdentifier
     */
    public function get()
    {
        $cartIdentifier = $this->session->getId();

        // when session is not started, returning empty string is behavior of session_id()
        if ($cartIdentifier === '') {
            $this->session->start();
            $cartIdentifier = $this->session->getId();
        }

        $customerIdentifier = new CustomerUserIdentifier($cartIdentifier, $this->currentCustomerUser->findCurrentUser());

        return $customerIdentifier;
    }

    /**
     * @param string $cartIdentifier
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserIdentifier
     */
    public function getOnlyWithCartIdentifier($cartIdentifier)
    {
        return new CustomerUserIdentifier($cartIdentifier, null);
    }
}

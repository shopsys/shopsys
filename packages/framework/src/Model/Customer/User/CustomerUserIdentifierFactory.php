<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomerUserIdentifierFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(CurrentCustomerUser $currentCustomerUser, SessionInterface $session)
    {
        $this->currentCustomerUser = $currentCustomerUser;
        $this->session = $session;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    public function get()
    {
        $cartIdentifier = $this->session->getId();

        // when session is not started, returning empty string is behavior of session_id()
        if ($cartIdentifier === '') {
            $this->session->start();
            $cartIdentifier = $this->session->getId();
        }

        $customerUserIdentifier = new CustomerUserIdentifier($cartIdentifier, $this->currentCustomerUser->findCurrentCustomerUser());

        return $customerUserIdentifier;
    }

    /**
     * @param string $cartIdentifier
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    public function getOnlyWithCartIdentifier($cartIdentifier)
    {
        return new CustomerUserIdentifier($cartIdentifier, null);
    }
}

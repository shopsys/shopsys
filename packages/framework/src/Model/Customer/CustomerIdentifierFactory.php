<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomerIdentifierFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    public function __construct(CurrentCustomer $currentCustomer, SessionInterface $session)
    {
        $this->currentCustomer = $currentCustomer;
        $this->session = $session;
    }

    public function get(): \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier
    {
        $cartIdentifier = $this->session->getId();

        // when session is not started, returning empty string is behaviour of session_id()
        if ($cartIdentifier === '') {
            $this->session->start();
            $cartIdentifier = $this->session->getId();
        }

        $customerIdentifier = new CustomerIdentifier($cartIdentifier, $this->currentCustomer->findCurrentUser());

        return $customerIdentifier;
    }

    public function getOnlyWithCartIdentifier(string $cartIdentifier): \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier
    {
        return new CustomerIdentifier($cartIdentifier, null);
    }
}

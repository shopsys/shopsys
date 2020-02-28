<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginAsUserFacade
{
    public const SESSION_LOGIN_AS = 'loginAsUser';

    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    protected $session;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository
     */
    protected $customerUserRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade
     */
    protected $administratorFrontSecurityFacade;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        CustomerUserRepository $customerUserRepository,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->customerUserRepository = $customerUserRepository;
        $this->administratorFrontSecurityFacade = $administratorFrontSecurityFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function rememberLoginAsUser(CustomerUser $customerUser)
    {
        $this->session->set(static::SESSION_LOGIN_AS, serialize($customerUser));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginAsRememberedUser(Request $request)
    {
        if (!$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException('Access denied');
        }

        if (!$this->session->has(static::SESSION_LOGIN_AS)) {
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException('User not set.');
        }

        /* @var $unserializedUser \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser */
        $unserializedUser = unserialize($this->session->get(static::SESSION_LOGIN_AS));

        $freshUser = $this->customerUserRepository->getCustomerUserById($unserializedUser->getId());

        $password = '';
        $firewallName = 'frontend';
        $token = new UsernamePasswordToken($freshUser, $password, $firewallName, $freshUser->getRoles());
        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }
}

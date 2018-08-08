<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Customer\UserRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginAsUserFacade
{
    const SESSION_LOGIN_AS = 'loginAsUser';

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
     * @var \Shopsys\FrameworkBundle\Model\Customer\UserRepository
     */
    protected $userRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade
     */
    protected $administratorFrontSecurityFacade;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        SessionInterface $session,
        UserRepository $userRepository,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->session = $session;
        $this->userRepository = $userRepository;
        $this->administratorFrontSecurityFacade = $administratorFrontSecurityFacade;
    }

    public function rememberLoginAsUser(User $user)
    {
        $this->session->set(self::SESSION_LOGIN_AS, serialize($user));
    }

    public function loginAsRememberedUser(Request $request)
    {
        if (!$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException('Access denied');
        }

        if (!$this->session->has(self::SESSION_LOGIN_AS)) {
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException('User not set.');
        }

        $unserializedUser = unserialize($this->session->get(self::SESSION_LOGIN_AS));
        /* @var $unserializedUser \Shopsys\FrameworkBundle\Model\Customer\User */
        $this->session->remove(self::SESSION_LOGIN_AS);
        $freshUser = $this->userRepository->getUserById($unserializedUser->getId());

        $password = '';
        $firewallName = 'frontend';
        $freshUserRoles = array_merge($freshUser->getRoles(), [Roles::ROLE_ADMIN_AS_CUSTOMER]);
        $token = new UsernamePasswordToken($freshUser, $password, $firewallName, $freshUserRoles);
        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);
    }
}

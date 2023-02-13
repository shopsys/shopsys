<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginAsRememberedUserException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginAsUserFacade
{
    public const SESSION_LOGIN_AS = 'loginAsUser';

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRepository $customerUserRepository
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly CustomerUserRepository $customerUserRepository,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     */
    public function rememberLoginAsUser(CustomerUser $customerUser)
    {
        $this->requestStack->getSession()->set(static::SESSION_LOGIN_AS, serialize($customerUser));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginAsRememberedUser(Request $request)
    {
        if (!$this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            throw new LoginAsRememberedUserException('Access denied');
        }

        if (!$this->requestStack->getSession()->has(static::SESSION_LOGIN_AS)) {
            throw new LoginAsRememberedUserException('User not set.');
        }

        /** @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $unserializedUser */
        $unserializedUser = unserialize($this->requestStack->getSession()->get(static::SESSION_LOGIN_AS));

        $freshUser = $this->customerUserRepository->getCustomerUserById($unserializedUser->getId());

        $firewallName = 'frontend';
        $token = new UsernamePasswordToken($freshUser, $firewallName, $freshUser->getRoles());
        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }
}

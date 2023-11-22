<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Exception;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class Authenticator
{
    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        protected readonly TokenStorageInterface $tokenStorage,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function checkLoginProcess(Request $request): bool
    {
        $error = null;

        if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
        } else {
            $session = $request->getSession();
            $error = $session->get(Security::AUTHENTICATION_ERROR);
            $session->remove(Security::AUTHENTICATION_ERROR);
        }

        if ($error !== null) {
            throw new LoginFailedException(
                'Log in failed.',
                $error instanceof Exception ? $error : null,
            );
        }

        return true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginUser(CustomerUser $customerUser, Request $request): void
    {
        $token = new UsernamePasswordToken(
            $customerUser,
            'frontend',
            $customerUser->getRoles(),
        );
        $this->tokenStorage->setToken($token);

        // dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);

        $request->getSession()->migrate();
    }
}

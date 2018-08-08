<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginService
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    private $tokenStorage;

    /**
     * @var \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    private $traceableEventDispatcher;

    public function __construct(
        TokenStorage $tokenStorage,
        TraceableEventDispatcher $traceableEventDispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->traceableEventDispatcher = $traceableEventDispatcher;
    }

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
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException('Login failed.');
        }

        return true;
    }

    public function loginUser(User $user, Request $request): void
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'frontend', $user->getRoles());
        $this->tokenStorage->setToken($token);

        // dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->traceableEventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);
    }
}

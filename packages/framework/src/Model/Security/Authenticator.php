<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class Authenticator
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected $traceableEventDispatcher;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
     * @param \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher $traceableEventDispatcher
     */
    public function __construct(
        TokenStorage $tokenStorage,
        TraceableEventDispatcher $traceableEventDispatcher
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->traceableEventDispatcher = $traceableEventDispatcher;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function checkLoginProcess(Request $request)
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
            if ($error instanceof LoginWithDefaultPasswordException) {
                $message = 'Oh, you just tried to log in using default credentials. We do not allow that on production'
                    . ' environment. If you are random hacker, please go somewhere else. If you are authorized user,'
                    . ' please use another account or contact developers and change password during deployment.';
            } else {
                $message = 'Log in failed.';
            }
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException($message);
        }

        return true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginUser(User $user, Request $request)
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'frontend', $user->getRoles());
        $this->tokenStorage->setToken($token);

        // dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->traceableEventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $event);

        $request->getSession()->migrate();
    }
}

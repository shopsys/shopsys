<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class Authenticator
{
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $tokenStorageInterface;

    /**
     * @var \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher
     */
    protected $traceableEventDispatcher;

    /**
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorageInterface
     * @param \Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher $traceableEventDispatcher
     */
    public function __construct(
        TokenStorageInterface $tokenStorageInterface,
        TraceableEventDispatcher $traceableEventDispatcher
    ) {
        $this->tokenStorageInterface = $tokenStorageInterface;
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
            throw new \Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException(
                'Log in failed.',
                $error instanceof \Exception ? $error : null
            );
        }

        return true;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginUser(CustomerUser $customerUser, Request $request)
    {
        $token = new UsernamePasswordToken($customerUser, $customerUser->getPassword(), 'frontend', $customerUser->getRoles());
        $this->tokenStorageInterface->setToken($token);

        // dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->traceableEventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);

        $request->getSession()->migrate();
    }
}

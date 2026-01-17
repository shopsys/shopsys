<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class CustomerLoginHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    protected RouterInterface $router;

    public function __construct(CurrentDomainRouter $router)
    {
        $this->router = $router;
    }

    #[Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): ?Response
    {
        $referer = $request->headers->get('referer');

        if ($request->isXmlHttpRequest()) {
            $responseData = [
                'success' => true,
                'urlToRedirect' => $referer,
            ];

            return new JsonResponse($responseData);
        }

        return new RedirectResponse($referer);
    }

    #[Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($request->isXmlHttpRequest()) {
            $responseData = [
                'success' => false,
            ];

            if ($exception instanceof TooManyLoginAttemptsAuthenticationException) {
                $responseData['reason'] = 'too-many-attempts';
            }

            return new JsonResponse($responseData);
        }
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        return new RedirectResponse($this->router->generate('front_login'));
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use GraphQL\Error\FormattedError;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class TokenAuthenticator extends AbstractAuthenticator
{
    protected const HEADER_AUTHORIZATION = 'Authorization';
    protected const BEARER = 'Bearer ';

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param \Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider $frontendApiUserProvider
     */
    public function __construct(
        protected readonly TokenFacade $tokenFacade,
        protected readonly FrontendApiUserProvider $frontendApiUserProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get(static::HEADER_AUTHORIZATION);

        if ($authorizationHeader === null) {
            throw new CustomUserMessageAuthenticationException('Authorization header not provided.');
        }

        $credentials = substr($authorizationHeader, strlen(static::BEARER));

        $token = $this->tokenFacade->getTokenByString($credentials);

        $email = $token->claims()->get(FrontendApiUser::CLAIM_EMAIL);

        return new Passport(
            new UserBadge($email, function () use ($token) {
                return $this->frontendApiUserProvider->loadUserByToken($token);
            }),
            new CustomCredentials(
                function (string $credentials) {
                    return $this->checkCredentials($credentials);
                },
                $credentials
            ),
        );
    }

    /**
     * @param string|null $credentials
     * @return bool
     */
    public function checkCredentials(?string $credentials): bool
    {
        $this->tokenFacade->getTokenByString($credentials);

        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has(static::HEADER_AUTHORIZATION) &&
            str_starts_with(
                $request->headers->get(static::HEADER_AUTHORIZATION),
                static::BEARER
            );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string $firewallName
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $responseData = [
            'errors' => [FormattedError::createFromException($exception)],
        ];

        return new JsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
    }
}

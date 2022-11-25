<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use GraphQL\Error\FormattedError;
use InvalidArgumentException;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUser;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    protected const HEADER_AUTHORIZATION = 'Authorization';

    protected const BEARER = 'Bearer ';

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     */
    protected $tokenFacade;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     */
    public function __construct(TokenFacade $tokenFacade)
    {
        $this->tokenFacade = $tokenFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has(static::HEADER_AUTHORIZATION) && strpos(
            $request->headers->get(static::HEADER_AUTHORIZATION),
            static::BEARER
        ) === 0;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string|null
     */
    public function getCredentials(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get(static::HEADER_AUTHORIZATION);

        return substr($authorizationHeader, strlen(static::BEARER));
    }

    /**
     * @param string|null $credentials
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     * @return \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?FrontendApiUser
    {
        if ($credentials === null) {
            return null;
        }

        if (!$userProvider instanceof FrontendApiUserProvider) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of %s ("%s" was given).',
                    FrontendApiUserProvider::class,
                    get_class($userProvider)
                )
            );
        }

        $token = $this->tokenFacade->getTokenByString($credentials);

        return $userProvider->loadUserByToken($token);
    }

    /**
     * @param string|null $credentials
     * @param \Shopsys\FrontendApiBundle\Model\User\FrontendApiUser $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        $this->tokenFacade->getTokenByString($credentials);

        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string $providerKey
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
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
            'errors' => FormattedError::createFromException($exception),
        ];

        return new JsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException|null $authException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $responseData = [
            'errors' => [
                'message' => 'Authentication Required',
                'extensions' => [
                    'category' => 'token',
                ],
            ],
        ];

        return new JsonResponse($responseData, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}

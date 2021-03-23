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
    /**
     * @deprecated Constant will be removed in nex major version. Use DIC property instead
     */
    protected const HEADER_AUTHORIZATION = 'Authorization';

    /**
     * @deprecated Constant will be removed in nex major version. Use DIC property instead
     */
    protected const BEARER = 'Bearer ';

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     */
    protected $tokenFacade;

    /**
     * @var string
     */
    protected string $authenticationHeader;

    /**
     * @var string
     */
    protected string $authenticationType;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Token\TokenFacade $tokenFacade
     * @param string $authenticationHeader
     * @param string $authenticationScheme
     */
    public function __construct(
        TokenFacade $tokenFacade,
        string $authenticationHeader = 'Authorization',
        string $authenticationScheme = 'Bearer '
    ) {
        $this->tokenFacade = $tokenFacade;
        $this->authenticationHeader = $authenticationHeader;
        $this->authenticationType = $authenticationScheme;

        if ($authenticationHeader === 'Authorization' && $authenticationHeader !== static::HEADER_AUTHORIZATION) {
            @trigger_error(
                sprintf(
                    'Don\'t override constants "%s" from "%s", use DIC property instead. Constants will be removed.',
                    'HEADER_AUTHORIZATION',
                    static::class
                ),
                E_USER_DEPRECATED
            );
            $this->authenticationHeader = static::HEADER_AUTHORIZATION;
        }

        if ($authenticationScheme !== 'Bearer ' || $authenticationScheme === static::BEARER) {
            return;
        }

        @trigger_error(
            sprintf(
                'Don\'t override constants "%s" from "%s", use DIC property instead. Constants will be removed.',
                'BEARER',
                static::class
            ),
            E_USER_DEPRECATED
        );
        $this->authenticationType = static::BEARER;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has($this->authenticationHeader) && strpos(
            $request->headers->get($this->authenticationHeader),
            $this->authenticationType
        ) === 0;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string|null
     */
    public function getCredentials(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get($this->authenticationHeader);

        return substr($authorizationHeader, strlen($this->authenticationType));
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
        $token = $this->tokenFacade->getTokenByString($credentials);
        $this->tokenFacade->validateToken($token);

        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string $providerKey
     * @return \Symfony\Component\HttpFoundation\Response|null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
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

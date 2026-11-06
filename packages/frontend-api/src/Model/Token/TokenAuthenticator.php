<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Token;

use GraphQL\Error\FormattedError;
use Lcobucci\JWT\Token\RegisteredClaims;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
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
    protected const HEADER_AUTHORIZATION = 'X-Auth-Token';
    protected const BEARER = 'Bearer ';
    protected const string ACCESS_TOKEN_COOKIE_NAME = 'accessToken';
    protected const string HEADER_DOMAIN_ID = 'X-Domain-Id';
    protected const string HEADER_ORIGIN = 'Origin';

    public function __construct(
        protected readonly TokenFacade $tokenFacade,
        protected readonly FrontendApiUserProvider $frontendApiUserProvider,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);

        if ($credentials === null) {
            throw new CustomUserMessageAuthenticationException('Authorization header not provided.');
        }

        $token = $this->tokenFacade->getTokenByString($credentials);

        $email = $token->claims()->get(FrontendApiUser::CLAIM_EMAIL);

        $uuid = $token->claims()->get(FrontendApiUser::CLAIM_UUID);
        $issuedAt = $token->claims()->get(RegisteredClaims::ISSUED_AT);

        if (!$this->customerUserFacade->isLastSecurityChangeOlderThan($uuid, $issuedAt)) {
            throw new InvalidTokenUserMessageException();
        }

        return new Passport(
            new UserBadge($email, function () use ($token) {
                return $this->frontendApiUserProvider->loadUserByToken($token);
            }),
            new CustomCredentials(
                function (string $credentials) {
                    return $this->checkCredentials($credentials);
                },
                $credentials,
            ),
        );
    }

    public function checkCredentials(?string $credentials): bool
    {
        $this->tokenFacade->getTokenByString($credentials);

        return true;
    }

    #[Override]
    public function supports(Request $request): bool
    {
        return $this->getCredentials($request) !== null;
    }

    #[Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    #[Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $formattedError = FormattedError::createFromException($exception);
        $formattedError['extensions']['userCode'] = $exception instanceof UserErrorWithCodeInterface
            ? $exception->getUserErrorCode()
            : 'invalid-token';

        $responseData = [
            'errors' => [$formattedError],
        ];

        return new JsonResponse($responseData, Response::HTTP_OK);
    }

    public function getCredentials(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get(static::HEADER_AUTHORIZATION);

        if ($authorizationHeader !== null) {
            return str_starts_with($authorizationHeader, static::BEARER)
                ? substr($authorizationHeader, strlen(static::BEARER))
                : null;
        }

        if (!$this->isCookieAuthenticationAllowed($request)) {
            return null;
        }

        return $request->cookies->get($this->getAccessTokenCookieName()) ?: null;
    }

    public function getAccessTokenCookieNameForDomainId(int $domainId): string
    {
        return sprintf('%s-%d', static::ACCESS_TOKEN_COOKIE_NAME, $domainId);
    }

    protected function getAccessTokenCookieName(): string
    {
        return $this->getAccessTokenCookieNameForDomainId($this->domain->getId());
    }

    protected function isCookieAuthenticationAllowed(Request $request): bool
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            return false;
        }

        if ($request->headers->get(static::HEADER_DOMAIN_ID) !== (string)$this->domain->getId()) {
            return false;
        }

        $origin = $request->headers->get(static::HEADER_ORIGIN);
        $domainOrigin = Request::create($this->domain->getUrl())->getSchemeAndHttpHost();

        return $origin !== null && strcasecmp(rtrim($origin, '/'), $domainOrigin) === 0;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Security;

use Override;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class McpTokenAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    protected const string HEADER_AUTHORIZATION = 'Authorization';
    protected const string BEARER_PREFIX = 'Bearer ';
    public const string REQUEST_ATTRIBUTE_ADMINISTRATOR_MCP_TOKEN = '_administrator_mcp_token';

    public function __construct(
        protected readonly AdministratorMcpTokenFacade $administratorMcpTokenFacade,
    ) {
    }

    #[Override]
    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/_mcp');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function authenticate(Request $request): Passport
    {
        $authorizationHeader = $request->headers->get(static::HEADER_AUTHORIZATION);

        if ($authorizationHeader === null) {
            throw new CustomUserMessageAuthenticationException('Authorization: Bearer token is required.');
        }

        if (!str_starts_with($authorizationHeader, static::BEARER_PREFIX)) {
            throw new CustomUserMessageAuthenticationException('Authorization header must use the Bearer scheme.');
        }

        $tokenString = substr($authorizationHeader, strlen(static::BEARER_PREFIX));
        $administratorMcpToken = $this->administratorMcpTokenFacade->findValidTokenByTokenString($tokenString);

        if ($administratorMcpToken === null) {
            throw new CustomUserMessageAuthenticationException('Invalid MCP token.');
        }

        $request->attributes->set(static::REQUEST_ATTRIBUTE_ADMINISTRATOR_MCP_TOKEN, $administratorMcpToken);

        return new SelfValidatingPassport(
            new UserBadge(
                (string)$administratorMcpToken->getAdministrator()->getId(),
            ),
        );
    }

    #[Override]
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $administratorMcpToken = $request->attributes->get(static::REQUEST_ATTRIBUTE_ADMINISTRATOR_MCP_TOKEN);

        if ($administratorMcpToken instanceof AdministratorMcpToken) {
            $this->administratorMcpTokenFacade->markTokenUsed($administratorMcpToken);
        }

        return null;
    }

    #[Override]
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return $this->createUnauthorizedResponse($exception->getMessageKey());
    }

    #[Override]
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->createUnauthorizedResponse('Authorization: Bearer token is required.');
    }

    protected function createUnauthorizedResponse(string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'error' => [
                    'code' => 'mcp-unauthorized',
                    'message' => $message,
                ],
            ],
            Response::HTTP_UNAUTHORIZED,
        );
    }
}

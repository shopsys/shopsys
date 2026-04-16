<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\OAuth;

use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpTokenFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthAuthorizationCodeFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthClientRegistrationFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthProtocol;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class McpOAuthTokenController
{
    public function __construct(
        protected readonly McpOAuthAuthorizationCodeFacade $mcpOauthAuthorizationCodeFacade,
        protected readonly McpOAuthClientRegistrationFacade $mcpOauthClientRegistrationFacade,
        protected readonly AdministratorMcpTokenFacade $administratorMcpTokenFacade,
        protected readonly AdministratorFacade $administratorFacade,
    ) {
    }

    #[Route(path: '/mcp/oauth/token', name: 'mcp_oauth_token', methods: [Request::METHOD_POST])]
    public function tokenAction(Request $request): JsonResponse
    {
        if ($request->request->get('grant_type') !== McpOAuthProtocol::GRANT_TYPE_AUTHORIZATION_CODE) {
            return $this->createErrorResponse(
                'unsupported_grant_type',
                sprintf('Only %s is supported.', McpOAuthProtocol::GRANT_TYPE_AUTHORIZATION_CODE),
            );
        }

        $clientId = $request->request->getString('client_id');
        $redirectUri = $request->request->getString('redirect_uri');
        $authorizationCode = $request->request->getString('code');
        $codeVerifier = $request->request->getString('code_verifier');
        $clientRegistration = $this->mcpOauthClientRegistrationFacade->findClientRegistrationDataByClientId($clientId);

        if ($clientRegistration === null) {
            return $this->createErrorResponse('invalid_client', 'Unknown client_id.');
        }

        if (!$this->mcpOauthClientRegistrationFacade->hasMatchingRedirectUri($clientRegistration, $redirectUri)) {
            return $this->createErrorResponse('invalid_grant', 'The redirect_uri does not match the registered client.');
        }

        $authorizationCodeData = $this->mcpOauthAuthorizationCodeFacade->consumeAuthorizationCode($authorizationCode);

        if ($authorizationCodeData === null) {
            return $this->createErrorResponse('invalid_grant', 'The authorization code is invalid or expired.');
        }

        if (
            $authorizationCodeData['client_id'] !== $clientId
            || !$this->mcpOauthClientRegistrationFacade->isMatchingRedirectUri($authorizationCodeData['redirect_uri'], $redirectUri)
            || !$this->isValidPkceCodeVerifier($codeVerifier, $authorizationCodeData['code_challenge'])
        ) {
            return $this->createErrorResponse('invalid_grant', 'The authorization code exchange is invalid.');
        }

        $administrator = $this->administratorFacade->getById($authorizationCodeData['administrator_id']);
        $issuedToken = $this->administratorMcpTokenFacade->issueTokenForAdministratorAndClient(
            $administrator,
            $authorizationCodeData['client_id'],
            $clientRegistration->clientName,
        );

        return new JsonResponse([
            'access_token' => $issuedToken->getTokenString(),
            'token_type' => McpOAuthProtocol::TOKEN_TYPE_BEARER,
            'expires_in' => $this->administratorMcpTokenFacade->getRemainingLifetimeInSeconds($issuedToken),
        ]);
    }

    protected function createErrorResponse(string $error, string $errorDescription): JsonResponse
    {
        return new JsonResponse([
            'error' => $error,
            'error_description' => $errorDescription,
        ], Response::HTTP_BAD_REQUEST);
    }

    protected function isValidPkceCodeVerifier(string $codeVerifier, string $expectedCodeChallenge): bool
    {
        $calculatedCodeChallenge = rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '=');

        return hash_equals($expectedCodeChallenge, $calculatedCodeChallenge);
    }
}

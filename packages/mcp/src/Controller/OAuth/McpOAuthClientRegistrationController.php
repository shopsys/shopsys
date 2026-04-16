<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\OAuth;

use InvalidArgumentException;
use JsonException;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArrayHelper;
use Shopsys\McpBundle\Model\OAuth\McpOAuthClientRegistrationFacade;
use Shopsys\McpBundle\Model\OAuth\McpOAuthProtocol;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class McpOAuthClientRegistrationController
{
    public function __construct(
        protected readonly McpOAuthClientRegistrationFacade $mcpOauthClientRegistrationFacade,
    ) {
    }

    #[Route(path: '/mcp/oauth/register', name: 'mcp_oauth_register', methods: [Request::METHOD_POST])]
    public function registerAction(Request $request): JsonResponse
    {
        try {
            $payload = $request->toArray();
            $registration = $this->mcpOauthClientRegistrationFacade->registerClient(
                ArrayHelper::getArrayOrEmpty($payload, 'redirect_uris'),
                ArrayHelper::getStringOrNull($payload, 'client_name'),
            );
        } catch (JsonException|InvalidArgumentException $exception) {
            return new JsonResponse([
                'error' => 'invalid_client_metadata',
                'error_description' => $exception->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'client_id' => $registration->clientId,
            'client_name' => $registration->clientName,
            'redirect_uris' => $registration->redirectUris,
            'grant_types' => [McpOAuthProtocol::GRANT_TYPE_AUTHORIZATION_CODE],
            'response_types' => [McpOAuthProtocol::RESPONSE_TYPE_CODE],
            'token_endpoint_auth_method' => McpOAuthProtocol::TOKEN_ENDPOINT_AUTH_METHOD_NONE,
        ], Response::HTTP_CREATED);
    }
}

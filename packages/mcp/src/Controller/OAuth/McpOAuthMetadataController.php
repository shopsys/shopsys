<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\OAuth;

use Shopsys\McpBundle\Component\Routing\McpRouteName;
use Shopsys\McpBundle\Model\OAuth\McpOAuthProtocol;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class McpOAuthMetadataController
{
    public function __construct(
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(path: '/.well-known/oauth-authorization-server', name: McpRouteName::MCP_OAUTH_METADATA, methods: [Request::METHOD_GET])]
    public function metadataAction(Request $request): JsonResponse
    {
        return new JsonResponse([
            'issuer' => $request->getSchemeAndHttpHost(),
            'authorization_endpoint' => $this->urlGenerator->generate(
                McpRouteName::ADMIN_MCP_OAUTH_AUTHORIZE,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            'token_endpoint' => $this->urlGenerator->generate(McpRouteName::MCP_OAUTH_TOKEN, [], UrlGeneratorInterface::ABSOLUTE_URL),
            'registration_endpoint' => $this->urlGenerator->generate(McpRouteName::MCP_OAUTH_REGISTER, [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_types_supported' => [McpOAuthProtocol::RESPONSE_TYPE_CODE],
            'grant_types_supported' => [McpOAuthProtocol::GRANT_TYPE_AUTHORIZATION_CODE],
            'token_endpoint_auth_methods_supported' => [McpOAuthProtocol::TOKEN_ENDPOINT_AUTH_METHOD_NONE],
            'code_challenge_methods_supported' => [McpOAuthProtocol::CODE_CHALLENGE_METHOD_S256],
        ]);
    }
}

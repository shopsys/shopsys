<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Controller\OAuth;

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

    #[Route(path: '/.well-known/oauth-authorization-server', name: 'mcp_oauth_metadata', methods: [Request::METHOD_GET])]
    public function metadataAction(Request $request): JsonResponse
    {
        return new JsonResponse([
            'issuer' => $request->getSchemeAndHttpHost(),
            'authorization_endpoint' => $this->urlGenerator->generate(
                'admin_superadmin_mcp_oauth_authorize',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL,
            ),
            'token_endpoint' => $this->urlGenerator->generate('mcp_oauth_token', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'registration_endpoint' => $this->urlGenerator->generate('mcp_oauth_register', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_types_supported' => [McpOAuthProtocol::RESPONSE_TYPE_CODE],
            'grant_types_supported' => [McpOAuthProtocol::GRANT_TYPE_AUTHORIZATION_CODE],
            'token_endpoint_auth_methods_supported' => [McpOAuthProtocol::TOKEN_ENDPOINT_AUTH_METHOD_NONE],
            'code_challenge_methods_supported' => [McpOAuthProtocol::CODE_CHALLENGE_METHOD_S256],
        ]);
    }
}

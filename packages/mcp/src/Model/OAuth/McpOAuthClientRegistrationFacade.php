<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

use InvalidArgumentException;
use Shopsys\McpBundle\Model\Administrator\McpToken\AdministratorMcpToken;
use Symfony\Component\HttpFoundation\IpUtils;

class McpOAuthClientRegistrationFacade
{
    protected const string DEFAULT_CLIENT_NAME = 'Unknown MCP client';

    public function __construct(
        protected readonly McpOAuthClientRegistrationStorage $mcpOAuthClientRegistrationStorage,
    ) {
    }

    /**
     * @param array<string> $redirectUris
     */
    public function registerClient(array $redirectUris, ?string $clientName): McpOAuthClientRegistrationData
    {
        $normalizedRedirectUris = $this->normalizeAndValidateRedirectUris($redirectUris);
        $registrationData = McpOAuthClientRegistrationData::createFromArray([
            'client_id' => bin2hex(random_bytes(16)),
            'client_name' => $this->normalizeAndValidateClientName($clientName),
            'redirect_uris' => $normalizedRedirectUris,
        ]);
        $this->mcpOAuthClientRegistrationStorage->save($registrationData);

        return $registrationData;
    }

    public function findClientRegistrationDataByClientId(string $clientId): ?McpOAuthClientRegistrationData
    {
        return $this->mcpOAuthClientRegistrationStorage->findByClientId($clientId);
    }

    public function findClientRegistrationByClientIdAndRedirectUri(
        ?string $clientId,
        ?string $redirectUri,
    ): ?McpOAuthClientRegistrationData {
        if ($clientId === null || $redirectUri === null) {
            return null;
        }

        $clientRegistrationData = $this->findClientRegistrationDataByClientId($clientId);

        if ($clientRegistrationData === null || !$this->hasMatchingRedirectUri($clientRegistrationData, $redirectUri)) {
            return null;
        }

        return $clientRegistrationData;
    }

    public function hasMatchingRedirectUri(
        McpOAuthClientRegistrationData $clientRegistrationData,
        string $redirectUri,
    ): bool {
        foreach ($clientRegistrationData->redirectUris as $registeredRedirectUri) {
            if ($this->isMatchingRedirectUri($registeredRedirectUri, $redirectUri)) {
                return true;
            }
        }

        return false;
    }

    public function isMatchingRedirectUri(string $expectedRedirectUri, string $redirectUri): bool
    {
        if ($expectedRedirectUri === $redirectUri) {
            return true;
        }

        $expectedRedirectUriParts = parse_url($expectedRedirectUri);
        $redirectUriParts = parse_url($redirectUri);

        if ($expectedRedirectUriParts === false || $redirectUriParts === false) {
            return false;
        }

        if (!$this->isAllowedLoopbackHttpRedirectUri($expectedRedirectUriParts)) {
            return false;
        }

        if (!$this->isAllowedLoopbackHttpRedirectUri($redirectUriParts)) {
            return false;
        }

        return $this->hasMatchingRedirectUriComponents($expectedRedirectUriParts, $redirectUriParts);
    }

    /**
     * @param array<string> $redirectUris
     * @return array<string>
     */
    protected function normalizeAndValidateRedirectUris(array $redirectUris): array
    {
        if ($redirectUris === []) {
            throw new InvalidArgumentException('At least one redirect URI is required.');
        }

        $normalizedRedirectUris = [];

        foreach ($redirectUris as $redirectUri) {
            if (!is_string($redirectUri) || $redirectUri === '') {
                throw new InvalidArgumentException('Redirect URIs must be non-empty strings.');
            }

            $redirectUriParts = parse_url($redirectUri);

            if ($redirectUriParts === false) {
                throw new InvalidArgumentException('Redirect URIs must be valid absolute URLs.');
            }

            $scheme = $redirectUriParts['scheme'] ?? null;
            $host = $redirectUriParts['host'] ?? null;

            if (
                ($scheme !== 'https')
                && !($scheme === 'http' && $this->isAllowedLoopbackHost($host))
            ) {
                throw new InvalidArgumentException('Redirect URIs must use HTTPS or localhost HTTP.');
            }

            $normalizedRedirectUris[] = $redirectUri;
        }

        return array_values(array_unique($normalizedRedirectUris));
    }

    protected function normalizeAndValidateClientName(?string $clientName): string
    {
        $normalizedClientName = $clientName ?? self::DEFAULT_CLIENT_NAME;

        if (mb_strlen($normalizedClientName) > AdministratorMcpToken::CLIENT_NAME_MAX_LENGTH) {
            return mb_substr($normalizedClientName, 0, AdministratorMcpToken::CLIENT_NAME_MAX_LENGTH);
        }

        return $normalizedClientName;
    }

    /**
     * @param array<string, int|string> $redirectUriParts
     */
    protected function isAllowedLoopbackHttpRedirectUri(array $redirectUriParts): bool
    {
        return ($redirectUriParts['scheme'] ?? null) === 'http'
            && $this->isAllowedLoopbackHost($redirectUriParts['host'] ?? null);
    }

    /**
     * @param array<string, int|string> $expectedRedirectUriParts
     * @param array<string, int|string> $redirectUriParts
     */
    protected function hasMatchingRedirectUriComponents(array $expectedRedirectUriParts, array $redirectUriParts): bool
    {
        foreach (['path', 'query', 'fragment', 'user', 'pass'] as $component) {
            if (($expectedRedirectUriParts[$component] ?? '') !== ($redirectUriParts[$component] ?? '')) {
                return false;
            }
        }

        return true;
    }

    protected function isAllowedLoopbackHost(?string $host): bool
    {
        if ($host === null) {
            return false;
        }

        $normalizedHost = trim($host, '[]');

        return $normalizedHost === 'localhost'
            || IpUtils::checkIp($normalizedHost, '127.0.0.0/8')
            || $normalizedHost === '::1';
    }
}

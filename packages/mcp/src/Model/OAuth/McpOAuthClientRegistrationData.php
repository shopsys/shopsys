<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\OAuth;

class McpOAuthClientRegistrationData
{
    public ?string $clientId = null;

    public ?string $clientName = null;

    /**
     * @var array<string>
     */
    public array $redirectUris = [];

    /**
     * @param array{client_id: string, client_name: string, redirect_uris: array<string>} $data
     */
    public static function createFromArray(array $data): self
    {
        $clientRegistrationData = new self();
        $clientRegistrationData->clientId = $data['client_id'];
        $clientRegistrationData->clientName = $data['client_name'];
        $clientRegistrationData->redirectUris = $data['redirect_uris'];

        return $clientRegistrationData;
    }

    /**
     * @return array{client_id: string, client_name: string, redirect_uris: array<string>}
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'client_name' => $this->clientName,
            'redirect_uris' => $this->redirectUris,
        ];
    }

    public function hasRedirectUri(string $redirectUri): bool
    {
        return in_array($redirectUri, $this->redirectUris, true);
    }
}

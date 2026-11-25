<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Packetery;

use Symfony\Component\OptionsResolver\OptionsResolver;

class PacketeryConfig
{
    protected bool $enabled;

    protected string $restApiUrl;

    protected string $apiPassword;

    protected string $sender;

    protected string $apiKey;

    public function __construct(array $packeteryConfig)
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver->setRequired([
            'enabled',
            'rest_api_url',
            'api_password',
            'sender',
        ]);

        $optionsResolver->setAllowedTypes(
            'enabled',
            'bool',
        );

        $optionsResolver->setAllowedTypes(
            'rest_api_url',
            'string',
        );

        $optionsResolver->setAllowedTypes(
            'api_password',
            'string',
        );

        $optionsResolver->setAllowedTypes(
            'sender',
            'string',
        );

        // the API key is needed only for the pickup point feeds, so the projects without them do not have to set it
        $optionsResolver->setDefault('api_key', '');
        $optionsResolver->setAllowedTypes('api_key', 'string');

        $packeteryConfig = $optionsResolver->resolve($packeteryConfig);

        $this->enabled = $packeteryConfig['enabled'];
        $this->restApiUrl = $packeteryConfig['rest_api_url'];
        $this->apiPassword = $packeteryConfig['api_password'];
        $this->sender = $packeteryConfig['sender'];
        $this->apiKey = $packeteryConfig['api_key'];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getRestApiUrl(): string
    {
        return $this->restApiUrl;
    }

    public function getApiPassword(): string
    {
        return $this->apiPassword;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

    public function isApiAllowed(): bool
    {
        return $this->isEnabled() &&
            $this->getRestApiUrl() !== '' &&
            $this->getApiPassword() !== '' &&
            $this->getSender() !== '';
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function isPickupPointFeedAllowed(): bool
    {
        return $this->isEnabled() && $this->getApiKey() !== '';
    }
}

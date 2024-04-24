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

    /**
     * @param array $packeteryConfig
     */
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

        $optionsResolver->resolve($packeteryConfig);

        $this->enabled = $packeteryConfig['enabled'];
        $this->restApiUrl = $packeteryConfig['rest_api_url'];
        $this->apiPassword = $packeteryConfig['api_password'];
        $this->sender = $packeteryConfig['sender'];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getRestApiUrl(): string
    {
        return $this->restApiUrl;
    }

    /**
     * @return string
     */
    public function getApiPassword(): string
    {
        return $this->apiPassword;
    }

    /**
     * @return string
     */
    public function getSender(): string
    {
        return $this->sender;
    }

    /**
     * @return bool
     */
    public function isApiAllowed(): bool
    {
        return $this->isEnabled() &&
            $this->getRestApiUrl() !== '' &&
            $this->getApiPassword() !== '' &&
            $this->getSender() !== '';
    }
}

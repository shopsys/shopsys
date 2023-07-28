<?php

declare(strict_types=1);

namespace App\Component\DataBridge;

use Symfony\Component\OptionsResolver\OptionsResolver;

class BridgeConfig
{
    private bool $enabled;

    private string $baseUri;

    private string $user;

    private string $password;

    /**
     * @param array $bridgeConfig
     */
    public function __construct(array $bridgeConfig)
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver->setRequired([
            'enabled',
            'base_uri',
            'user',
            'password',
        ]);

        $optionsResolver->setAllowedTypes(
            'enabled',
            ['bool'],
        );
        $optionsResolver->setAllowedTypes(
            'base_uri',
            ['string'],
        );
        $optionsResolver->setAllowedTypes(
            'user',
            ['string'],
        );
        $optionsResolver->setAllowedTypes(
            'password',
            ['string'],
        );

        $optionsResolver->resolve($bridgeConfig);

        $this->enabled = $bridgeConfig['enabled'];
        $this->baseUri = $bridgeConfig['base_uri'];
        $this->user = $bridgeConfig['user'];
        $this->password = $bridgeConfig['password'];
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
    public function getBaseUri(): string
    {
        return $this->baseUri;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}

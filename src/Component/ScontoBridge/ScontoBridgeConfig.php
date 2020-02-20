<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ScontoBridgeConfig
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $baseUri;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @param array $scontoBridgeConfig
     */
    public function __construct(array $scontoBridgeConfig)
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
            ['bool']
        );
        $optionsResolver->setAllowedTypes(
            'base_uri',
            ['string']
        );
        $optionsResolver->setAllowedTypes(
            'user',
            ['string']
        );
        $optionsResolver->setAllowedTypes(
            'password',
            ['string']
        );

        $optionsResolver->resolve($scontoBridgeConfig);

        $this->enabled = $scontoBridgeConfig['enabled'];
        $this->baseUri = $scontoBridgeConfig['base_uri'];
        $this->user = $scontoBridgeConfig['user'];
        $this->password = $scontoBridgeConfig['password'];
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

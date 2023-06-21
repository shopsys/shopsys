<?php

declare(strict_types=1);

namespace App\Component\Akeneo;

use Symfony\Component\OptionsResolver\OptionsResolver;

class AkeneoConfig
{
    private bool $enabled;

    private string $baseUri;

    private string $clientId;

    private string $secret;

    private string $user;

    private string $password;

    /**
     * @param array $akeneoConfig
     */
    public function __construct(array $akeneoConfig)
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver->setRequired([
            'enabled',
            'base_uri',
            'client_id',
            'secret',
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
            'client_id',
            ['string'],
        );
        $optionsResolver->setAllowedTypes(
            'secret',
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

        $optionsResolver->resolve($akeneoConfig);

        $this->enabled = $akeneoConfig['enabled'];
        $this->baseUri = $akeneoConfig['base_uri'];
        $this->clientId = $akeneoConfig['client_id'];
        $this->secret = $akeneoConfig['secret'];
        $this->user = $akeneoConfig['user'];
        $this->password = $akeneoConfig['password'];
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
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->secret;
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

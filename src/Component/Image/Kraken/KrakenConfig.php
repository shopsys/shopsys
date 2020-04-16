<?php

declare(strict_types=1);

namespace App\Component\Image\Kraken;

use Symfony\Component\OptionsResolver\OptionsResolver;

class KrakenConfig
{
    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var bool
     */
    private $sandbox;

    /**
     * @var bool
     */
    private $lossy;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiSecret;

    /**
     * @param array $krakenConfig
     */
    public function __construct(array $krakenConfig)
    {
        $optionsResolver = new OptionsResolver();

        $optionsResolver->setRequired([
            'enabled',
            'sandbox',
            'lossy',
            'api_key',
            'api_secret',
        ]);

        $optionsResolver->setAllowedTypes(
            'enabled',
            ['bool']
        );
        $optionsResolver->setAllowedTypes(
            'sandbox',
            ['bool']
        );
        $optionsResolver->setAllowedTypes(
            'lossy',
            ['bool']
        );
        $optionsResolver->setAllowedTypes(
            'api_key',
            ['string']
        );
        $optionsResolver->setAllowedTypes(
            'api_secret',
            ['string']
        );

        $optionsResolver->resolve($krakenConfig);

        $this->enabled = $krakenConfig['enabled'];
        $this->sandbox = $krakenConfig['sandbox'];
        $this->lossy = $krakenConfig['lossy'];
        $this->apiKey = $krakenConfig['api_key'];
        $this->apiSecret = $krakenConfig['api_secret'];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    /**
     * @return bool
     */
    public function isLossy(): bool
    {
        return $this->lossy;
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getApiSecret(): string
    {
        return $this->apiSecret;
    }
}

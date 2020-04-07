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
            'api_key',
            'api_secret',
        ]);

        $optionsResolver->setAllowedTypes(
            'enabled',
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

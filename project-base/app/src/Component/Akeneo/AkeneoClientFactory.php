<?php

declare(strict_types=1);

namespace App\Component\Akeneo;

use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;

class AkeneoClientFactory
{
    /**
     * @param \App\Component\Akeneo\AkeneoConfig $akeneoConfig
     */
    public function __construct(private AkeneoConfig $akeneoConfig)
    {
    }

    /**
     * @return \Akeneo\Pim\ApiClient\AkeneoPimClientInterface
     */
    public function createClient(): AkeneoPimClientInterface
    {
        $clientBuilder = new AkeneoPimClientBuilder($this->akeneoConfig->getBaseUri());

        return $clientBuilder->buildAuthenticatedByPassword(
            $this->akeneoConfig->getClientId(),
            $this->akeneoConfig->getSecret(),
            $this->akeneoConfig->getUser(),
            $this->akeneoConfig->getPassword(),
        );
    }
}

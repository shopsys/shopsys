<?php

declare(strict_types=1);

namespace App\Component\Akeneo;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientBuilder;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;

class AkeneoClientFactory
{
    /**
     * @param \App\Component\Akeneo\AkeneoConfig $akeneoConfig
     */
    public function __construct(private AkeneoConfig $akeneoConfig)
    {
    }

    /**
     * @return \Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface
     */
    public function createClient(): AkeneoPimEnterpriseClientInterface
    {
        $clientBuilder = new AkeneoPimEnterpriseClientBuilder($this->akeneoConfig->getBaseUri());

        return $clientBuilder->buildAuthenticatedByPassword(
            $this->akeneoConfig->getClientId(),
            $this->akeneoConfig->getSecret(),
            $this->akeneoConfig->getUser(),
            $this->akeneoConfig->getPassword(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Component\Akeneo;

use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientBuilder;
use Akeneo\PimEnterprise\ApiClient\AkeneoPimEnterpriseClientInterface;

class AkeneoClientFactory
{
    /**
     * @var \App\Component\Akeneo\AkeneoConfig
     */
    private $akeneoConfig;

    /**
     * @param \App\Component\Akeneo\AkeneoConfig $akeneoConfig
     */
    public function __construct(AkeneoConfig $akeneoConfig)
    {
        $this->akeneoConfig = $akeneoConfig;
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
            $this->akeneoConfig->getPassword()
        );
    }
}

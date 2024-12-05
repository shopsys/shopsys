<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\OAuth;

use Convertim\OAuth\ConvertimOAuth;
use Convertim\OAuth\ConvertimOAuthFactory;
use Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider;

class OAuthFactory
{
    /**
     * @param \Shopsys\ConvertimBundle\Model\Convertim\ConvertimConfigProvider $convertimConfigProvider
     */
    public function __construct(
        protected readonly ConvertimConfigProvider $convertimConfigProvider,
    ) {
    }

    /**
     * @return \Convertim\OAuth\ConvertimOAuth
     */
    public function createConvertimOauth(): ConvertimOAuth
    {
        $convertimConfig = $this->convertimConfigProvider->getConfigForCurrentDomain();

        return (new ConvertimOAuthFactory())->createConvertimOAuth([
            ConvertimOAuth::OPTION_CLIENT_ID => $convertimConfig->clientId,
            ConvertimOAuth::OPTION_CLIENT_SECRET => $convertimConfig->clientSecret,
            ConvertimOAuth::OPTION_IS_PRODUCTION_MODE => $convertimConfig->isProductionMode,
        ]);
    }
}

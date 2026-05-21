<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\SocialNetwork;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class FedcmAdapterFactory
{
    public function __construct(
        protected readonly SocialNetworkConfigFactory $socialNetworkConfigFactory,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Returns a FedCM adapter for the given provider and domain, or null when:
     *  - FedCM is globally disabled,
     *  - the provider has fedcm.enabled === false,
     *  - the domain is not in the provider's enabledOnDomains,
     *  - the configured adapter class does not implement FedcmAdapterInterface.
     */
    public function createForDomainAndType(int $domainId, string $loginType): ?FedcmAdapterInterface
    {
        if (!$this->socialNetworkConfigFactory->isFedcmEnabledForDomain($domainId, $loginType)) {
            return null;
        }

        // HybridAuth OAuth2::configure() requires a syntactically valid callback URL even though FedCM does not use
        // redirect-based flows. We reuse the existing `front_social_network_login` route so the callback matches
        // the redirect URI registered with the IdP (Seznam validates redirect_uri during code-for-token exchange;
        // Google's tokeninfo endpoint ignores it).
        $domainRouter = $this->domainRouterFactory->getRouter($domainId);
        $callbackUrl = $domainRouter->generate(
            'front_social_network_login',
            ['type' => $loginType],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        $config = $this->socialNetworkConfigFactory->createConfigForDomain($domainId, $callbackUrl);
        $providerConfig = $config['providers'][$loginType] ?? null;

        if (!is_array($providerConfig)) {
            return null;
        }

        $providerConfig['callback'] = $callbackUrl;

        $adapterClass = $providerConfig['adapter'] ?? null;

        if (!is_string($adapterClass) || !is_subclass_of($adapterClass, FedcmAdapterInterface::class)) {
            $this->logger->warning(sprintf(
                'FedCM is enabled for provider "%s" but its configured `adapter` does not implement %s. '
                . 'Falling back to no FedCM support — check social_network_config.yaml.',
                $loginType,
                FedcmAdapterInterface::class,
            ));

            return null;
        }

        return new $adapterClass($providerConfig);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay;

use GoPay\Definition\TokenScope;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotConfiguredException;
use Shopsys\FrameworkBundle\Model\GoPay\Exception\GoPayNotEnabledOnDomainException;

class GoPayClientFactory
{
    protected const string PRODUCTION_URL = 'https://gate.gopay.cz/';
    protected const string TEST_URL = 'https://gw.sandbox.gopay.com/';

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Model\GoPay\GoPayClient>
     */
    protected array $clientsByDomainId = [];

    /**
     * @param array $clientConfigs
     */
    public function __construct(
        protected array $clientConfigs,
    ) {
    }

    /**
     * @param array $gopayConfig
     * @return \Shopsys\FrameworkBundle\Model\GoPay\GoPayClient
     */
    protected function createInstance(array $gopayConfig): GoPayClient
    {
        return new GoPayClient($gopayConfig);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\GoPay\GoPayClient
     */
    public function createByDomain(DomainConfig $domainConfig): GoPayClient
    {
        if (!array_key_exists($domainConfig->getId(), $this->clientsByDomainId)) {
            $gopayConfig = $this->mapConfigByDomain($domainConfig);
            $this->clientsByDomainId[$domainConfig->getId()] = $this->createInstance($gopayConfig);
        }

        return $this->clientsByDomainId[$domainConfig->getId()];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return array
     */
    protected function mapConfigByDomain(DomainConfig $domainConfig): array
    {
        foreach ($this->clientConfigs as $clientConfig) {
            if (!in_array($domainConfig->getId(), $clientConfig['domains'], true)) {
                continue;
            }

            return $this->createConfig(
                $clientConfig['goid'],
                $clientConfig['clientId'],
                $clientConfig['clientSecret'],
                $clientConfig['isProductionMode'] ? static::PRODUCTION_URL : static::TEST_URL,
                $domainConfig->getLocale(),
            );
        }

        throw new GoPayNotEnabledOnDomainException('GoPay is not enabled on domain with ID "' . $domainConfig->getId() . '"');
    }

    /**
     * @param string $goid
     * @param string $clientId
     * @param string $clientSecret
     * @param string $gatewayUrl
     * @param string $locale
     * @return array
     */
    protected function createConfig(
        string $goid,
        string $clientId,
        string $clientSecret,
        string $gatewayUrl,
        string $locale,
    ): array {
        if ($goid === '' || $clientId === '' || $clientSecret === '') {
            throw new GoPayNotConfiguredException();
        }

        return [
            'goid' => $goid,
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'gatewayUrl' => $gatewayUrl,
            'scope' => TokenScope::ALL,
            'language' => $locale,
            'timeout' => 30,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\DomainFactory as BaseDomainFactory;

/**
 * @property \App\Component\Setting\Setting $setting
 */
class DomainFactory extends BaseDomainFactory
{
    /**
     * @param mixed $domainsConfigFilepath
     * @param mixed $domainsUrlsConfigFilepath
     */
    public function create($domainsConfigFilepath, $domainsUrlsConfigFilepath)
    {
        throw new \BadMethodCallException('Use method `createWithCdn()` instead.');
    }

    /**
     * @param string $domainsConfigFilepath
     * @param string $domainsUrlsConfigFilepath
     * @param string $cdnDomain
     * @return \App\Component\Domain\Domain
     */
    public function createWithCdn(string $domainsConfigFilepath, string $domainsUrlsConfigFilepath, string $cdnDomain)
    {
        // When you do not want to use CDN, it is used value '//' as workaround by https://github.com/symfony/symfony/issues/28391
        if (empty(trim($cdnDomain, '/')) === false) {
            $resolvedCdnDomain = $cdnDomain;
        } else {
            $resolvedCdnDomain = null;
        }

        $domainConfigs = $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, $domainsUrlsConfigFilepath);
        $domain = new Domain($domainConfigs, $this->setting, $resolvedCdnDomain);

        $domainId = getenv('DOMAIN');
        if ($domainId !== false) {
            $domain->switchDomainById((int)$domainId);
        }

        return $domain;
    }
}

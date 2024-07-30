<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class DomainFactoryOverwritingDomainUrl
{
    protected ?string $overwriteDomainUrl = null;

    /**
     * @param string|null $overwriteDomainUrl
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader $domainsConfigLoader
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        $overwriteDomainUrl,
        protected readonly DomainsConfigLoader $domainsConfigLoader,
        protected readonly Setting $setting,
    ) {
        $this->overwriteDomainUrl = $overwriteDomainUrl;
    }

    /**
     * @param string $domainsConfigFilepath
     * @param string $domainsUrlsConfigFilepath
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    public function create($domainsConfigFilepath, $domainsUrlsConfigFilepath)
    {
        $domainConfigs = $this->domainsConfigLoader->loadDomainConfigsFromYaml(
            $domainsConfigFilepath,
            $domainsUrlsConfigFilepath,
        );

        if ($this->overwriteDomainUrl !== null) {
            $domainConfigs = $this->overwriteDomainUrl($domainConfigs);
        }

        $domain = new Domain($domainConfigs, $this->setting);

        $domainId = getenv('DOMAIN');

        if ($domainId !== false) {
            $domain->switchDomainById((int)$domainId);
        }

        return $domain;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function overwriteDomainUrl(array $domainConfigs)
    {
        $mockedDomainConfigs = [];

        foreach ($domainConfigs as $domainConfig) {
            $mockedDomainConfigs[] = new DomainConfig(
                $domainConfig->getId(),
                $this->overwriteDomainUrl,
                $domainConfig->getName(),
                $domainConfig->getLocale(),
                $domainConfig->getDateTimeZone(),
                $domainConfig->getStylesDirectory(),
                $domainConfig->getDesignId(),
                $domainConfig->getType(),
            );
        }

        return $mockedDomainConfigs;
    }
}

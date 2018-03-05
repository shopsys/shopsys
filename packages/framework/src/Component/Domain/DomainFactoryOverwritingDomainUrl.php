<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader;
use Shopsys\FrameworkBundle\Component\Setting\Setting;

class DomainFactoryOverwritingDomainUrl
{
    /**
     * @var string|null
     */
    private $overwriteDomainUrl;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader
     */
    private $domainsConfigLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    private $setting;

    /**
     * @param string|null $overwriteDomainUrl
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader $domainsConfigLoader
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    public function __construct($overwriteDomainUrl, DomainsConfigLoader $domainsConfigLoader, Setting $setting)
    {
        $this->overwriteDomainUrl = $overwriteDomainUrl;
        $this->domainsConfigLoader = $domainsConfigLoader;
        $this->setting = $setting;
    }

    /**
     * @param string $domainsConfigFilepath
     * @param string $domainsUrlsConfigFilepath
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    public function create($domainsConfigFilepath, $domainsUrlsConfigFilepath)
    {
        $domainConfigs = $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, $domainsUrlsConfigFilepath);
        if ($this->overwriteDomainUrl !== null) {
            $domainConfigs = $this->overwriteDomainUrl($domainConfigs);
        }

        $domain = new Domain($domainConfigs, $this->setting);

        $domainId = getenv('DOMAIN');
        if ($domainId !== false) {
            $domain->switchDomainById($domainId);
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
                $domainConfig->getStylesDirectory()
            );
        }

        return $mockedDomainConfigs;
    }
}

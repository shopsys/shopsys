<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;

class DomainFactoryOverwritingDomainUrl
{
    public function __construct(
        protected readonly ?string $overwriteDomainUrl,
        protected readonly DomainsConfigLoader $domainsConfigLoader,
        protected readonly Setting $setting,
        protected readonly CurrentAdministrator $currentAdministrator,
    ) {
    }

    public function create(string $domainsConfigFilepath, string $domainsUrlsConfigFilepath): Domain
    {
        $domainConfigs = $this->domainsConfigLoader->loadDomainConfigsFromYaml(
            $domainsConfigFilepath,
            $domainsUrlsConfigFilepath,
        );

        if ($this->overwriteDomainUrl !== null) {
            $domainConfigs = $this->overwriteDomainUrl($domainConfigs);
        }

        $domain = new Domain(
            $domainConfigs,
            $this->setting,
            $this->currentAdministrator,
        );

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
    public function overwriteDomainUrl(array $domainConfigs): array
    {
        $mockedDomainConfigs = [];

        foreach ($domainConfigs as $domainConfig) {
            $mockedDomainConfigs[] = new DomainConfig(
                $domainConfig->getId(),
                $this->overwriteDomainUrl,
                $domainConfig->getName(),
                $domainConfig->getLocale(),
                $domainConfig->getDateTimeZone(),
                $this->overwriteDomainUrl,
                $domainConfig->getType(),
                $domainConfig->isAllowedInDataFixtures(),
                $domainConfig->getPostfix(),
            );
        }

        return $mockedDomainConfigs;
    }
}

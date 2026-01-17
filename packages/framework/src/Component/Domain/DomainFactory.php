<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;

class DomainFactory
{
    public function __construct(
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
}

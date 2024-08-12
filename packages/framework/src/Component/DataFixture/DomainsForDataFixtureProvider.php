<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class DomainsForDataFixtureProvider
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected array $allowedDemoDataDomains = [];

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($domainConfig->isAllowedInDataFixtures()) {
                $this->allowedDemoDataDomains[$domainConfig->getId()] = $domainConfig;
            }
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllowedDemoDataDomains(): array
    {
        return array_values($this->allowedDemoDataDomains);
    }

    /**
     * @return int[]
     */
    public function getAllowedDemoDataDomainIds(): array
    {
        return array_map(
            static fn (DomainConfig $domainConfig) => $domainConfig->getId(),
            $this->getAllowedDemoDataDomains(),
        );
    }

    /**
     * @return string[]
     */
    public function getAllowedDemoDataLocales(): array
    {
        $allowedLocales = array_map(
            static fn (DomainConfig $domainConfig) => $domainConfig->getLocale(),
            $this->getAllowedDemoDataDomains(),
        );

        return array_unique($allowedLocales);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    public function getFirstAllowedDomainConfig(): DomainConfig
    {
        return reset($this->allowedDemoDataDomains);
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isDomainIdAllowed(int $domainId): bool
    {
        return array_key_exists($domainId, $this->allowedDemoDataDomains);
    }
}

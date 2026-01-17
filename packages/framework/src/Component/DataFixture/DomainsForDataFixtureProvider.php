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
    protected ?array $allowedDemoDataDomains = null;

    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function getAllowedDemoDataDomains(): array
    {
        if ($this->allowedDemoDataDomains === null) {
            foreach ($this->domain->getAll() as $domainConfig) {
                if ($domainConfig->isAllowedInDataFixtures()) {
                    $this->allowedDemoDataDomains[$domainConfig->getId()] = $domainConfig;
                }
            }
        }

        return $this->allowedDemoDataDomains;
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

    public function getFirstAllowedDomainConfig(): DomainConfig
    {
        $allowedDomains = $this->getAllowedDemoDataDomains();

        return reset($allowedDomains);
    }

    public function isDomainIdAllowed(int $domainId): bool
    {
        return array_key_exists($domainId, $this->getAllowedDemoDataDomains());
    }
}

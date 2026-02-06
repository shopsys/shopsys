<?php

declare(strict_types=1);

namespace Shopsys\Cli\Config;

use Symfony\Component\DependencyInjection\ServiceLocator;

final class ConfigSectionRegistry
{
    public function __construct(
        private readonly ServiceLocator $domainConfigSections,
        private readonly ServiceLocator $projectConfigSections,
    ) {
    }

    /**
     * @return \Shopsys\Cli\Config\DomainConfigSectionInterface[]
     */
    public function getDomainConfigSections(): array
    {
        /** @var \Shopsys\Cli\Config\DomainConfigSectionInterface[] $configSections */
        $configSections = $this->getConfigSectionsBySectionLocator($this->domainConfigSections);

        return $configSections;
    }

    /**
     * @return \Shopsys\Cli\Config\ProjectConfigSectionInterface[]
     */
    public function getProjectConfigSections(): array
    {
        /** @var \Shopsys\Cli\Config\ProjectConfigSectionInterface[] $configSections */
        $configSections = $this->getConfigSectionsBySectionLocator($this->projectConfigSections);

        return $configSections;
    }

    /**
     * @return \Shopsys\Cli\Config\ConfigSectionInterface[]
     */
    private function getConfigSectionsBySectionLocator(ServiceLocator $configSectionLocator): array
    {
        $configSections = [];

        foreach ($configSectionLocator->getProvidedServices() as $serviceId => $metadata) {
            // new instance is returned each time to avoid shared state between domains thanks to the service locator and shared: false in services.yaml
            $configSections[$serviceId] = $configSectionLocator->get($serviceId);
        }

        return $configSections;
    }
}

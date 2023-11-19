<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Plugin;

class PluginDataFixtureFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureRegistry $pluginDataFixtureRegistry
     */
    public function __construct(protected readonly PluginDataFixtureRegistry $pluginDataFixtureRegistry)
    {
    }

    public function loadAll(): void
    {
        $pluginDataFixtures = $this->pluginDataFixtureRegistry->getDataFixtures();

        foreach ($pluginDataFixtures as $pluginDataFixture) {
            $pluginDataFixture->load();
        }
    }
}

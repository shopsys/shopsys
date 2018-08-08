<?php

namespace Shopsys\FrameworkBundle\Component\Plugin;

class PluginDataFixtureFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Plugin\PluginDataFixtureRegistry
     */
    protected $pluginDataFixtureRegistry;

    public function __construct(PluginDataFixtureRegistry $pluginDataFixtureRegistry)
    {
        $this->pluginDataFixtureRegistry = $pluginDataFixtureRegistry;
    }

    public function loadAll(): void
    {
        $pluginDataFixtures = $this->pluginDataFixtureRegistry->getDataFixtures();
        foreach ($pluginDataFixtures as $pluginDataFixture) {
            $pluginDataFixture->load();
        }
    }
}

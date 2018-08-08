<?php

namespace Shopsys\FrameworkBundle\Component\Plugin;

use Shopsys\Plugin\PluginDataFixtureInterface;

class PluginDataFixtureRegistry
{
    /**
     * @var \Shopsys\Plugin\PluginDataFixtureInterface[]
     */
    private $pluginDataFixtures = [];

    public function registerDataFixture(PluginDataFixtureInterface $pluginDataFixture): void
    {
        $this->pluginDataFixtures[] = $pluginDataFixture;
    }

    /**
     * @return \Shopsys\Plugin\PluginDataFixtureInterface[]
     */
    public function getDataFixtures(): array
    {
        return $this->pluginDataFixtures;
    }
}

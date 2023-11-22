<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Plugin;

use Shopsys\Plugin\PluginDataFixtureInterface;

class PluginDataFixtureRegistry
{
    /**
     * @var \Shopsys\Plugin\PluginDataFixtureInterface[]
     */
    protected array $pluginDataFixtures = [];

    /**
     * @param \Shopsys\Plugin\PluginDataFixtureInterface $pluginDataFixture
     */
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

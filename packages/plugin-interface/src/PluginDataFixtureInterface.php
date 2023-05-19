<?php

declare(strict_types=1);

namespace Shopsys\Plugin;

interface PluginDataFixtureInterface
{
    /**
     * Loads plugin demo data
     */
    public function load();
}

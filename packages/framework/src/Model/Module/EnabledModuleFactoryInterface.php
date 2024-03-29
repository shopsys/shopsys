<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

interface EnabledModuleFactoryInterface
{
    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Module\EnabledModule
     */
    public function create(string $name): EnabledModule;
}

<?php

namespace Shopsys\FrameworkBundle\Model\Module;

class EnabledModuleFactory implements EnabledModuleFactoryInterface
{

    public function create(string $name): EnabledModule
    {
        return new EnabledModule($name);
    }
}

<?php

namespace Shopsys\FrameworkBundle\Model\Module;

interface EnabledModuleFactoryInterface
{

    public function create(string $name): EnabledModule;
}

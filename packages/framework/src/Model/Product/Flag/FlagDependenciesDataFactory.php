<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagDependenciesDataFactory
{
    public function create(): FlagDependenciesData
    {
        return $this->createInstance();
    }

    protected function createInstance(): FlagDependenciesData
    {
        return new FlagDependenciesData();
    }
}

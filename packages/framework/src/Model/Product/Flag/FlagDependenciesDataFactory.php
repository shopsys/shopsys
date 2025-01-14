<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

class FlagDependenciesDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDependenciesData
     */
    public function create(): FlagDependenciesData
    {
        return $this->createInstance();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDependenciesData
     */
    protected function createInstance(): FlagDependenciesData
    {
        return new FlagDependenciesData();
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ParameterGroupFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
     */
    public function create(ParameterGroupData $data): ParameterGroup;
}

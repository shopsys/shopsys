<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

interface ParameterGroupDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData
     */
    public function create(): ParameterGroupData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    public function fillNew(ParameterGroupData $parameterGroupData): void;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup $parameterGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData
     */
    public function createFromParameterGroup(ParameterGroup $parameterGroup): ParameterGroupData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup $parameterGroup
     */
    public function fillFromParameterGroup(
        ParameterGroupData $parameterGroupData,
        ParameterGroup $parameterGroup,
    ): void;
}

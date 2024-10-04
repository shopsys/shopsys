<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterGroupDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData
     */
    protected function createInstance(): ParameterGroupData
    {
        return new ParameterGroupData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData
     */
    public function create(): ParameterGroupData
    {
        $parameterGroupData = $this->createInstance();
        $this->fillNew($parameterGroupData);

        return $parameterGroupData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    public function fillNew(ParameterGroupData $parameterGroupData): void
    {
        $parameterGroupData->position = 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup $parameterGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData
     */
    public function createFromParameterGroup(ParameterGroup $parameterGroup): ParameterGroupData
    {
        $parameterGroupData = $this->createInstance();
        $this->fillFromParameterGroup($parameterGroupData, $parameterGroup);

        return $parameterGroupData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup $parameterGroup
     */
    public function fillFromParameterGroup(
        ParameterGroupData $parameterGroupData,
        ParameterGroup $parameterGroup,
    ): void {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroupTranslation[] $translations */
        $translations = $parameterGroup->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $parameterGroupData->name = $names;

        $parameterGroupData->position = $parameterGroup->getPosition();
    }
}

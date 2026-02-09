<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ParameterGroupDataFactory
{
    protected function createInstance(): ParameterGroupData
    {
        return new ParameterGroupData();
    }

    public function create(): ParameterGroupData
    {
        $parameterGroupData = $this->createInstance();
        $this->fillNew($parameterGroupData);

        return $parameterGroupData;
    }

    public function fillNew(ParameterGroupData $parameterGroupData): void
    {
        $parameterGroupData->position = 0;
    }

    public function createFromParameterGroup(ParameterGroup $parameterGroup): ParameterGroupData
    {
        $parameterGroupData = $this->createInstance();
        $this->fillFromParameterGroup($parameterGroupData, $parameterGroup);

        return $parameterGroupData;
    }

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

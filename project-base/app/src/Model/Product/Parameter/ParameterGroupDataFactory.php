<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

class ParameterGroupDataFactory
{
    /**
     * @return \App\Model\Product\Parameter\ParameterGroupData
     */
    public function create(): ParameterGroupData
    {
        $parameterGroupData = new ParameterGroupData();
        $this->fillNew($parameterGroupData);

        return $parameterGroupData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     */
    private function fillNew(ParameterGroupData $parameterGroupData): void
    {
        $parameterGroupData->orderingPriority = 0;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroup $parameterGroup
     * @return \App\Model\Product\Parameter\ParameterGroupData
     */
    public function createFromParameterGroup(ParameterGroup $parameterGroup): ParameterGroupData
    {
        $parameterGroupData = new ParameterGroupData();
        $this->fillFromParameterGroup($parameterGroupData, $parameterGroup);

        return $parameterGroupData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterGroupData $parameterGroupData
     * @param \App\Model\Product\Parameter\ParameterGroup $parameterGroup
     */
    private function fillFromParameterGroup(
        ParameterGroupData $parameterGroupData,
        ParameterGroup $parameterGroup,
    ): void {
        /** @var \App\Model\Product\Parameter\ParameterGroupTranslation[] $translations */
        $translations = $parameterGroup->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $parameterGroupData->names = $names;

        $parameterGroupData->orderingPriority = $parameterGroup->getOrderingPriority();
    }
}

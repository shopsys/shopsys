<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Unit;

class ParameterUnitDataFactory
{
    /**
     * @return \App\Model\Product\Parameter\Unit\ParameterUnitData
     */
    public function create(): ParameterUnitData
    {
        return new ParameterUnitData();
    }

    /**
     * @param \App\Model\Product\Parameter\Unit\ParameterUnit $parameterUnit
     * @return \App\Model\Product\Parameter\Unit\ParameterUnitData
     */
    public function createFromParameterUnit(ParameterUnit $parameterUnit): ParameterUnitData
    {
        $parameterUnitData = $this->create();
        $parameterUnitData->akeneoCode = $parameterUnit->getAkeneoCode();

        /** @var \App\Model\Product\Parameter\Unit\ParameterUnitTranslation[] $translations */
        $translations = $parameterUnit->getTranslations();
        foreach ($translations as $translation) {
            $parameterUnitData->name[$translation->getLocale()] = $translation->getName();
        }

        return $parameterUnitData;
    }
}

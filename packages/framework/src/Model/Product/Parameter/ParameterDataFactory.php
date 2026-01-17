<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ParameterDataFactory
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    protected function createInstance(): ParameterData
    {
        return new ParameterData();
    }

    public function create(): ParameterData
    {
        $parameterData = $this->createInstance();
        $this->fillNew($parameterData);

        return $parameterData;
    }

    protected function fillNew(ParameterData $parameterData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterData->name[$locale] = null;
        }

        $parameterData->orderingPriority = 0;
        $parameterData->parameterType = Parameter::PARAMETER_TYPE_COMMON;
    }

    public function createFromParameter(Parameter $parameter): ParameterData
    {
        $parameterData = $this->createInstance();
        $this->fillFromParameter($parameterData, $parameter);

        return $parameterData;
    }

    protected function fillFromParameter(ParameterData $parameterData, Parameter $parameter)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation[] $translations */
        $translations = $parameter->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }

        $parameterData->name = $names;
        $parameterData->uuid = $parameter->getUuid();
        $parameterData->parameterType = $parameter->getParameterType();
        $parameterData->unit = $parameter->getUnit();
        $parameterData->orderingPriority = $parameter->getOrderingPriority();
        $parameterData->group = $parameter->getGroup();
    }
}

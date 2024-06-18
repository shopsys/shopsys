<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ParameterDataFactory implements ParameterDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData
     */
    protected function createInstance(): ParameterData
    {
        return new ParameterData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData
     */
    public function create(): ParameterData
    {
        $parameterData = $this->createInstance();
        $this->fillNew($parameterData);

        return $parameterData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     */
    protected function fillNew(ParameterData $parameterData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $parameterData->name[$locale] = null;
        }

        $parameterData->orderingPriority = 0;
        $parameterData->parameterType = Parameter::PARAMETER_TYPE_COMMON;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData
     */
    public function createFromParameter(Parameter $parameter): ParameterData
    {
        $parameterData = $this->createInstance();
        $this->fillFromParameter($parameterData, $parameter);

        return $parameterData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterData $parameterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     */
    protected function fillFromParameter(ParameterData $parameterData, Parameter $parameter)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterTranslation[] $translations */
        $translations = $parameter->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }

        $parameterData->name = $names;
        $parameterData->visible = $parameter->isVisible();
        $parameterData->uuid = $parameter->getUuid();
        $parameterData->parameterType = $parameter->getParameterType();
        $parameterData->unit = $parameter->getUnit();
        $parameterData->orderingPriority = $parameter->getOrderingPriority();
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory;

class ParameterValueDataFactory
{
    public function __construct(protected UploadedFileDataFactory $uploadedFileDataFactory)
    {
    }

    protected function createInstance(): ParameterValueData
    {
        return new ParameterValueData();
    }

    public function create(): ParameterValueData
    {
        $parameterValueData = $this->createInstance();
        $parameterValueData->rgbHex = null;
        $parameterValueData->colorIcon = $this->uploadedFileDataFactory->create();

        return $parameterValueData;
    }

    public function createFromParameterValue(ParameterValue $parameterValue): ParameterValueData
    {
        $parameterValueData = $this->createInstance();
        $this->fillFromParameterValue($parameterValueData, $parameterValue);
        $parameterValueData->colorIcon = $this->uploadedFileDataFactory->createByEntity($parameterValue);

        return $parameterValueData;
    }

    protected function fillFromParameterValue(ParameterValueData $parameterValueData, ParameterValue $parameterValue)
    {
        $parameterValueData->text = $parameterValue->getText();
        $parameterValueData->numericValue = $parameterValue->getNumericValue();
        $parameterValueData->locale = $parameterValue->getLocale();
        $parameterValueData->rgbHex = $parameterValue->getRgbHex();
        $parameterValueData->colorIcon = $this->uploadedFileDataFactory->create();
    }
}

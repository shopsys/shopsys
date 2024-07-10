<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactoryInterface;

class ParameterValueDataFactory implements ParameterValueDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactory $uploadedFileDataFactory
     */
    public function __construct(protected UploadedFileDataFactoryInterface $uploadedFileDataFactory)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData
     */
    protected function createInstance(): ParameterValueData
    {
        return new ParameterValueData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData
     */
    public function create(): ParameterValueData
    {
        $parameterValueData = $this->createInstance();
        $parameterValueData->rgbHex = null;
        $parameterValueData->colourIcon = $this->uploadedFileDataFactory->create();

        return $parameterValueData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData
     */
    public function createFromParameterValue(ParameterValue $parameterValue): ParameterValueData
    {
        $parameterValueData = $this->createInstance();
        $this->fillFromParameterValue($parameterValueData, $parameterValue);
        $parameterValueData->colourIcon = $this->uploadedFileDataFactory->createByEntity($parameterValue);

        return $parameterValueData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue
     */
    protected function fillFromParameterValue(ParameterValueData $parameterValueData, ParameterValue $parameterValue)
    {
        $parameterValueData->text = $parameterValue->getText();
        $parameterValueData->numericValue = $parameterValue->getNumericValue();
        $parameterValueData->locale = $parameterValue->getLocale();
        $parameterValueData->uuid = $parameterValue->getUuid();
        $parameterValueData->rgbHex = $parameterValue->getRgbHex();
        $parameterValueData->colourIcon = $this->uploadedFileDataFactory->create();
    }
}

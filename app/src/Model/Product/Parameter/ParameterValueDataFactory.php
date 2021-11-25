<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue as BaseParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData as BaseParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory as BaseParameterValueDataFactory;

class ParameterValueDataFactory extends BaseParameterValueDataFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactoryInterface
     */
    protected $uploadedFileDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\UploadedFile\UploadedFileDataFactoryInterface $uploadedFileDataFactory
     */
    public function __construct(UploadedFileDataFactoryInterface $uploadedFileDataFactory)
    {
        $this->uploadedFileDataFactory = $uploadedFileDataFactory;
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterValueData
     */
    public function create(): BaseParameterValueData
    {
        $parameterValueData = new ParameterValueData();
        $this->fillNew($parameterValueData);

        return $parameterValueData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     */
    protected function fillNew(BaseParameterValueData $parameterValueData): void
    {
        $parameterValueData->rgbHex = null;
        $parameterValueData->colourIcon = $this->uploadedFileDataFactory->create();
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     * @return \App\Model\Product\Parameter\ParameterValueData
     */
    public function createFromParameterValue(BaseParameterValue $parameterValue): BaseParameterValueData
    {
        $parameterValueData = new ParameterValueData();
        $this->fillFromParameterValue($parameterValueData, $parameterValue);
        $parameterValueData->colourIcon = $this->uploadedFileDataFactory->createByEntity($parameterValue);

        return $parameterValueData;
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @param \App\Model\Product\Parameter\ParameterValue $parameterValue
     */
    protected function fillFromParameterValue(BaseParameterValueData $parameterValueData, BaseParameterValue $parameterValue)
    {
        parent::fillFromParameterValue($parameterValueData, $parameterValue);

        $parameterValueData->rgbHex = $parameterValue->getRgbHex();
        $parameterValueData->colourIcon = $this->uploadedFileDataFactory->create();
    }
}

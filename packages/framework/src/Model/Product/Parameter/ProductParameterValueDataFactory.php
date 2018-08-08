<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValueDataFactory implements ProductParameterValueDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface
     */
    protected $parameterValueDataFactory;

    public function __construct(ParameterValueDataFactoryInterface $parameterValueDataFactory)
    {
        $this->parameterValueDataFactory = $parameterValueDataFactory;
    }

    public function create(): ProductParameterValueData
    {
        return new ProductParameterValueData();
    }

    public function createFromProductParameterValue(ProductParameterValue $productParameterValue): ProductParameterValueData
    {
        $productParameterValueData = new ProductParameterValueData();
        $this->fillFromProductParameterValue($productParameterValueData, $productParameterValue);

        return $productParameterValueData;
    }

    protected function fillFromProductParameterValue(ProductParameterValueData $productParameterValueData, ProductParameterValue $productParameterValue): void
    {
        $productParameterValueData->parameter = $productParameterValue->getParameter();
        $productParameterValueData->parameterValueData = $this->parameterValueDataFactory->createFromParameterValue($productParameterValue->getValue());
    }
}

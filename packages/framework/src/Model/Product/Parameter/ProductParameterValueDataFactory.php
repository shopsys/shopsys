<?php

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

class ProductParameterValueDataFactory implements ProductParameterValueDataFactoryInterface
{
    protected ParameterValueDataFactoryInterface $parameterValueDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface $parameterValueDataFactory
     */
    public function __construct(ParameterValueDataFactoryInterface $parameterValueDataFactory)
    {
        $this->parameterValueDataFactory = $parameterValueDataFactory;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    protected function createInstance(): ProductParameterValueData
    {
        return new ProductParameterValueData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    public function create(): ProductParameterValueData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData
     */
    public function createFromProductParameterValue(ProductParameterValue $productParameterValue): ProductParameterValueData
    {
        $productParameterValueData = $this->createInstance();
        $this->fillFromProductParameterValue($productParameterValueData, $productParameterValue);

        return $productParameterValueData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValueData $productParameterValueData
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     */
    protected function fillFromProductParameterValue(ProductParameterValueData $productParameterValueData, ProductParameterValue $productParameterValue)
    {
        $productParameterValueData->parameter = $productParameterValue->getParameter();
        $productParameterValueData->parameterValueData = $this->parameterValueDataFactory->createFromParameterValue(
            $productParameterValue->getValue()
        );
    }
}

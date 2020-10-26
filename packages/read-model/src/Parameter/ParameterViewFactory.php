<?php

declare(strict_types=1);

namespace Shopsys\ReadModelBundle\Parameter;

use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;

class ParameterViewFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue $productParameterValue
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView
     */
    public function createFromProductParameterValue(ProductParameterValue $productParameterValue): ParameterView
    {
        return new ParameterView(
            $productParameterValue->getParameter()->getId(),
            $productParameterValue->getParameter()->getName(),
            $productParameterValue->getValue()->getId(),
            $productParameterValue->getValue()->getText()
        );
    }

    /**
     * @param array $parameterArray
     * @return \Shopsys\ReadModelBundle\Parameter\ParameterView
     */
    public function createFromParameterArray(array $parameterArray): ParameterView
    {
        return new ParameterView(
            $parameterArray['parameter_id'],
            $parameterArray['parameter_name'],
            $parameterArray['parameter_value_id'],
            $parameterArray['parameter_value_text']
        );
    }
}

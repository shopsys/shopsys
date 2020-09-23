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
}

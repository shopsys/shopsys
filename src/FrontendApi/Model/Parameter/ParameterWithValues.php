<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Parameter;

use App\Model\Product\Unit\Unit;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValues as BaseParameterWithValues;

/**
 * @property \App\Model\Product\Parameter\Parameter $parameter
 */
class ParameterWithValues extends BaseParameterWithValues
{
    /**
     * @return string|null
     */
    public function getGroup(): ?string
    {
        $group = $this->parameter->getGroup();

        if ($group === null) {
            return null;
        }

        return $group->getName();
    }

    /**
     * @return \App\Model\Product\Unit\Unit|null
     */
    public function getUnit(): ?Unit
    {
        return $this->parameter->getUnit();
    }
}

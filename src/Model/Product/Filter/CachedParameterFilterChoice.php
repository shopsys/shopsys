<?php

declare(strict_types=1);


namespace App\Model\Product\Filter;

use App\Model\Product\Parameter\CachedParameter;
use App\Model\Product\Parameter\CachedParameterValue;
use App\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice as BaseParameterFilterChoice;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter as BaseParameter;

class CachedParameterFilterChoice extends BaseParameterFilterChoice
{
    /**
     * @param \App\Model\Product\Parameter\Parameter|null $parameter
     * @param \App\Model\Product\Parameter\ParameterValue[] $values
     */
    public function __construct(?BaseParameter $parameter = null, array $values = [])
    {
        parent::__construct($parameter, $values);

        /** @var Parameter $parameter */
        $this->setParameter($parameter);
        $this->setParameterValues($values);
    }

    /**
     * @param \App\Model\Product\Parameter\ParameterValue[] $parameterValues
     */
    private function setParameterValues(array $parameterValues): void
    {
        $cachedParameterValues = [];
        foreach ($parameterValues as $parameterValue) {
            $cachedParameterValues[] = new CachedParameterValue($parameterValue);
        }
        $this->values = $cachedParameterValues;
    }

    /**
     * @param \App\Model\Product\Parameter\Parameter|null $parameter
     */
    private function setParameter(?Parameter $parameter): void
    {
        if ($parameter !== null) {
            $this->parameter = new CachedParameter($parameter);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper as BaseProductFilterDataMapper;

class ProductFilterDataMapper extends BaseProductFilterDataMapper
{
    /**
     * This method is identical as in Base class, but only change here is using ParameterFilterData from App namespace
     *
     * @see https://github.com/shopsys/shopsys/issues/1625 issue on Github for more info
     * @param array $parameterAndValueUuids
     * @return \App\Model\Product\Filter\ParameterFilterData[]
     */
    protected function getParametersAndValuesByUuids(array $parameterAndValueUuids): array
    {
        $parametersFilterData = [];

        $this->loadParametersAndValuesFromArray($parameterAndValueUuids);

        foreach ($parameterAndValueUuids as $parameterAndValueUuid) {
            if (!array_key_exists($parameterAndValueUuid['parameter'], $this->parametersByUuid)) {
                continue;
            }

            /** @var \App\Model\Product\Parameter\Parameter $parameter */
            $parameter = $this->parametersByUuid[$parameterAndValueUuid['parameter']];

            $parameterValues = [];

            foreach ($parameterAndValueUuid['values'] as $parameterValueUuid) {
                if (!array_key_exists($parameterValueUuid, $this->parameterValuesByUuid)) {
                    continue;
                }

                /** @var \App\Model\Product\Parameter\ParameterValue $parameterValue */
                $parameterValue = $this->parameterValuesByUuid[$parameterValueUuid];

                $parameterValues[] = $parameterValue;
            }

            $parameterFilterData = new ParameterFilterData();
            $parameterFilterData->parameter = $parameter;
            $parameterFilterData->values = $parameterValues;

            $parametersFilterData[] = $parameterFilterData;
        }

        return $parametersFilterData;
    }
}

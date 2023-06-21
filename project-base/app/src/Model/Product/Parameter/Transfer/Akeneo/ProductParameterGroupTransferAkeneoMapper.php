<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Model\Product\Parameter\ParameterGroup;
use App\Model\Product\Parameter\ParameterGroupData;
use App\Model\Product\Parameter\ParameterGroupDataFactory;

class ProductParameterGroupTransferAkeneoMapper
{
    /**
     * @param \App\Model\Product\Parameter\ParameterGroupDataFactory $parameterGroupDataFactory
     */
    public function __construct(private ParameterGroupDataFactory $parameterGroupDataFactory)
    {
    }

    /**
     * @param array $akeneoParameterGroupData
     * @param \App\Model\Product\Parameter\ParameterGroup|null $parameterGroup
     * @return \App\Model\Product\Parameter\ParameterGroupData
     */
    public function mapAkeneoParameterGroupDataToParameterGroupData(
        array $akeneoParameterGroupData,
        ?ParameterGroup $parameterGroup,
    ): ParameterGroupData {
        if ($parameterGroup === null) {
            $parameterGroupData = $this->parameterGroupDataFactory->create();
            $parameterGroupData->akeneoCode = $akeneoParameterGroupData['code'];
        } else {
            $parameterGroupData = $this->parameterGroupDataFactory->createFromParameterGroup($parameterGroup);
        }

        $parameterGroupData->names['cs'] = $akeneoParameterGroupData['labels']['cs_CZ'];
        $parameterGroupData->names['sk'] = $akeneoParameterGroupData['labels']['sk_SK'];
        $parameterGroupData->orderingPriority = $akeneoParameterGroupData['sort_order'];

        return $parameterGroupData;
    }
}

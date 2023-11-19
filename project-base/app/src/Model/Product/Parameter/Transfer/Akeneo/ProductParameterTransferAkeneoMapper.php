<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use App\Model\Product\Parameter\Parameter;
use App\Model\Product\Parameter\ParameterData;
use App\Model\Product\Parameter\ParameterDataFactory;
use App\Model\Product\Parameter\ParameterGroupFacade;

class ProductParameterTransferAkeneoMapper
{
    /**
     * @param \App\Model\Product\Parameter\ParameterDataFactory $parameterDataFactory
     * @param \App\Model\Product\Parameter\ParameterGroupFacade $parameterGroupFacade
     */
    public function __construct(
        private ParameterDataFactory $parameterDataFactory,
        private ParameterGroupFacade $parameterGroupFacade,
    ) {
    }

    /**
     * @param mixed[] $akeneoParameterData
     * @param \App\Model\Product\Parameter\Parameter|null $parameter
     * @return \App\Model\Product\Parameter\ParameterData
     */
    public function mapAkeneoParameterDataToParameterData(
        array $akeneoParameterData,
        ?Parameter $parameter,
    ): ParameterData {
        if ($parameter === null) {
            $parameterData = $this->parameterDataFactory->create();
            $parameterData->akeneoCode = $akeneoParameterData['code'];
        } else {
            $parameterData = $this->parameterDataFactory->createFromParameter($parameter);
        }

        $parameterData->akeneoType = $akeneoParameterData['type'];
        $parameterData->orderingPriority = $akeneoParameterData['sort_order'];

        $parameterData->name['cs'] = $akeneoParameterData['labels']['cs_CZ'];
        $parameterData->name['sk'] = $akeneoParameterData['labels']['sk_SK'];

        $parameterData->group = $this->parameterGroupFacade->findParameterGroupByAkeneoCode($akeneoParameterData['group']);

        return $parameterData;
    }
}

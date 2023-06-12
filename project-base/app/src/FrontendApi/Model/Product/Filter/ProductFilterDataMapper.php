<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product\Filter;

use App\Model\Product\Filter\ParameterFilterData;
use App\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper as BaseProductFilterDataMapper;

/**
 * @property \App\Model\Product\Flag\FlagFacade $flagFacade
 * @property \App\Model\Product\Brand\BrandFacade $brandFacade
 * @property \App\Model\Product\Parameter\ParameterFacade $parameterFacade
 * @property \App\Model\Product\Parameter\Parameter[] $parametersByUuid
 * @property \App\Model\Product\Parameter\ParameterValue[] $parameterValuesByUuid
 */
class ProductFilterDataMapper extends BaseProductFilterDataMapper
{
    /**
     * @param \App\Model\Product\Flag\FlagFacade $flagFacade
     * @param \App\Model\Product\Brand\BrandFacade $brandFacade
     * @param \App\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ParameterFacade $parameterFacade,
        private ProductFilterDataFactory $productFilterDataFactory,
    ) {
        parent::__construct($flagFacade, $brandFacade, $parameterFacade);
    }

    /**
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
            $parameterFilterData->minimalValue = $parameterAndValueUuid['minimalValue'];
            $parameterFilterData->maximalValue = $parameterAndValueUuid['maximalValue'];

            $parametersFilterData[] = $parameterFilterData;
        }

        return $parametersFilterData;
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param array $frontendApiFilter
     * @return \App\Model\Product\Filter\ProductFilterData
     */
    public function mapFrontendApiFilterToProductFilterData(array $frontendApiFilter): ProductFilterData
    {
        $productFilterData = $this->productFilterDataFactory->create();
        $productFilterData->minimalPrice = $frontendApiFilter['minimalPrice'] ?? null;
        $productFilterData->maximalPrice = $frontendApiFilter['maximalPrice'] ?? null;
        $productFilterData->parameters = $this->getParametersAndValuesByUuids($frontendApiFilter['parameters'] ?? []);
        $productFilterData->inStock = $frontendApiFilter['onlyInStock'] ?? false;
        $productFilterData->brands = [];
        $productFilterData->flags = [];

        if (isset($frontendApiFilter['brands'])) {
            $productFilterData->brands = $this->brandFacade->getByUuids($frontendApiFilter['brands']);
        }

        if (isset($frontendApiFilter['flags'])) {
            $productFilterData->flags = $this->flagFacade->getByUuids($frontendApiFilter['flags']);
        }

        return $productFilterData;
    }
}

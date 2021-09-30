<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterDataMapper
{
    use SetterInjectionTrait;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected FlagFacade $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    protected BrandFacade $brandFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade
     */
    protected ParameterFacade $parameterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    protected array $parametersByUuid = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    protected array $parameterValuesByUuid = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory|null
     */
    protected ?ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade $parameterFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory|null $productFilterDataFactory
     */
    public function __construct(
        FlagFacade $flagFacade,
        BrandFacade $brandFacade,
        ParameterFacade $parameterFacade,
        ?ProductFilterDataFactory $productFilterDataFactory = null
    ) {
        $this->flagFacade = $flagFacade;
        $this->brandFacade = $brandFacade;
        $this->parameterFacade = $parameterFacade;
        $this->productFilterDataFactory = $productFilterDataFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setParameterBag(ProductFilterDataFactory $productFilterDataFactory): void
    {
        $this->setDependency($productFilterDataFactory, 'productFilterDataFactory');
    }

    /**
     * @param array $frontendApiFilter
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData
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

    /**
     * @param array $parameterAndValueUuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData[]
     */
    protected function getParametersAndValuesByUuids(array $parameterAndValueUuids): array
    {
        $parametersFilterData = [];

        $this->loadParametersAndValuesFromArray($parameterAndValueUuids);

        foreach ($parameterAndValueUuids as $parameterAndValueUuid) {
            if (!array_key_exists($parameterAndValueUuid['parameter'], $this->parametersByUuid)) {
                continue;
            }

            $parameter = $this->parametersByUuid[$parameterAndValueUuid['parameter']];

            $parameterValues = [];

            foreach ($parameterAndValueUuid['values'] as $parameterValueUuid) {
                if (!array_key_exists($parameterValueUuid, $this->parameterValuesByUuid)) {
                    continue;
                }

                $parameterValues[] = $this->parameterValuesByUuid[$parameterValueUuid];
            }

            $parameterFilterData = new ParameterFilterData();
            $parameterFilterData->parameter = $parameter;
            $parameterFilterData->values = $parameterValues;

            $parametersFilterData[] = $parameterFilterData;
        }

        return $parametersFilterData;
    }

    /**
     * @param array $parameterAndValueUuids
     */
    protected function loadParametersAndValuesFromArray(array $parameterAndValueUuids): void
    {
        $parameterUuids = [];
        $parameterValueUuids = [];

        foreach ($parameterAndValueUuids as $parameterAndValueUuid) {
            $parameterUuids[] = $parameterAndValueUuid['parameter'];

            foreach ($parameterAndValueUuid['values'] as $parameterValueUuid) {
                $parameterValueUuids[] = $parameterValueUuid;
            }
        }

        $this->parametersByUuid = $this->parameterFacade->getParametersByUuids($parameterUuids);
        $this->parameterValuesByUuid = $this->parameterFacade->getParameterValuesByUuids($parameterValueUuids);
    }
}

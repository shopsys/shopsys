<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Closure;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterDataMapper
{
    protected const string ENTITIES_CACHE_NAMESPACE = 'productFilterDataMapperEntities';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    protected array $parametersByUuid = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    protected array $parameterValuesByUuid = [];

    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly BrandFacade $brandFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly ProductFilterDataFactory $productFilterDataFactory,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

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
            $productFilterData->brands = $this->getEntitiesByUuids(
                'brands',
                $frontendApiFilter['brands'],
                $this->brandFacade->getByUuids(...),
            );
        }

        if (isset($frontendApiFilter['flags'])) {
            $productFilterData->flags = $this->getEntitiesByUuids(
                'flags',
                $frontendApiFilter['flags'],
                $this->flagFacade->getByUuids(...),
            );
        }

        return $productFilterData;
    }

    /**
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

        $this->parametersByUuid = $this->getEntitiesByUuids(
            'parameters',
            $parameterUuids,
            $this->parameterFacade->getParametersByUuids(...),
        );
        $this->parameterValuesByUuid = $this->getEntitiesByUuids(
            'parameterValues',
            $parameterValueUuids,
            $this->parameterFacade->getParameterValuesByUuids(...),
        );
    }

    /**
     * The same filter is often mapped several times in one request (e.g. for each flag of a list),
     * so the entities are loaded only once, while the mapped filter data are always created anew
     *
     * @template T
     * @param string[] $uuids
     * @param \Closure(string[]): T $loadEntitiesByUuids
     * @return T
     */
    protected function getEntitiesByUuids(string $entityName, array $uuids, Closure $loadEntitiesByUuids): mixed
    {
        sort($uuids);

        return $this->inMemoryCache->getOrSaveValue(
            static::ENTITIES_CACHE_NAMESPACE,
            static fn (): mixed => $loadEntitiesByUuids($uuids),
            $entityName,
            ...$uuids,
        );
    }
}

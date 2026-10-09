<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryParameterFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade;

class ProductFilterOptionsBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly ModuleFacade $moduleFacade,
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductOnCurrentDomainElasticFacade $productOnCurrentDomainElasticFacade,
        protected readonly CategoryParameterFacade $categoryParameterFacade,
        protected readonly ProductFilterOptionsFactory $productFilterOptionsFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsBatchLoadData[] $batchLoadDataIndexedByKey
     */
    public function loadByBatchLoadData(array $batchLoadDataIndexedByKey): Promise
    {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->promiseAdapter->all(array_map(
                fn (): ProductFilterOptions => $this->productFilterOptionsFactory->createProductFilterOptionsInstance(),
                $batchLoadDataIndexedByKey,
            ));
        }

        $productFilterConfigs = $this->productFilterFacade->getProductFilterConfigsByBatchLoadData($batchLoadDataIndexedByKey);
        $productFilterCountData = $this->productOnCurrentDomainElasticFacade->getProductFilterCountDataByBatchLoadData($batchLoadDataIndexedByKey);
        $collapsedParametersIndexedByCategoryId = $this->getCollapsedParametersIndexedByCategoryId($batchLoadDataIndexedByKey);
        $productFilterOptions = [];

        foreach ($batchLoadDataIndexedByKey as $key => $batchLoadData) {
            $entity = $batchLoadData->getEntity();

            $productFilterOptions[$key] = $this->productFilterOptionsFactory->createProductFilterOptionsByBatchLoadData(
                $batchLoadData,
                $productFilterConfigs[$key],
                $productFilterCountData[$key],
                $entity instanceof Category ? $collapsedParametersIndexedByCategoryId[$entity->getId()] : [],
            );
        }

        return $this->promiseAdapter->all($productFilterOptions);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterOptionsBatchLoadData[] $batchLoadDataIndexedByKey
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]> indexed by category id
     */
    protected function getCollapsedParametersIndexedByCategoryId(array $batchLoadDataIndexedByKey): array
    {
        $categoriesIndexedById = [];

        foreach ($batchLoadDataIndexedByKey as $batchLoadData) {
            $entity = $batchLoadData->getEntity();

            if ($entity instanceof Category) {
                $categoriesIndexedById[$entity->getId()] = $entity;
            }
        }

        if ($categoriesIndexedById === []) {
            return [];
        }

        return $this->categoryParameterFacade->getParametersCollapsedIndexedByCategoryId(array_values($categoriesIndexedById));
    }
}

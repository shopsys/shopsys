<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;

class ProductFilterConfigFactory
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductFilterElasticFacade $productFilterElasticFacade,
        protected readonly ParameterFacade $parameterFacade,
        protected readonly FlagFacade $flagFacade,
        protected readonly BrandFacade $brandFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $parameterChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[] $flagChoices
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[] $brandChoices
     */
    public function create(
        array $parameterChoices,
        array $flagChoices,
        array $brandChoices,
        PriceRange $priceRange,
    ): ProductFilterConfig {
        return new ProductFilterConfig($parameterChoices, $flagChoices, $brandChoices, $priceRange);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData[] $batchLoadDataIndexedByKey
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig[] indexed by the same keys as the batch load data
     */
    public function createByBatchLoadData(array $batchLoadDataIndexedByKey, string $locale): array
    {
        if ($batchLoadDataIndexedByKey === []) {
            return [];
        }

        $productFilterConfigIdsDataIndexedByKey = $this->productFilterElasticFacade->getProductFilterConfigIdsDataByBatchLoadData(
            $batchLoadDataIndexedByKey,
            $this->currentCustomerUser->getPricingGroup(),
        );
        $categoriesIndexedByKey = $this->getCategoriesIndexedByKey($batchLoadDataIndexedByKey);
        $flagsIndexedById = $this->getFlagsIndexedById($productFilterConfigIdsDataIndexedByKey, $locale);
        $brandsIndexedById = $this->getBrandsIndexedById($productFilterConfigIdsDataIndexedByKey);
        $parameterFilterChoicesIndexedByKey = $this->parameterFacade->getParameterFilterChoicesByIdsIndexedByKey(
            $this->getParameterValueIdsByParameterIdIndexedByKey($productFilterConfigIdsDataIndexedByKey, $categoriesIndexedByKey),
            $locale,
        );
        $sortedParameterIdsIndexedByCategoryId = $this->parameterFacade->getParameterIdsSortedByPositionIndexedByCategoryId(
            array_values($categoriesIndexedByKey),
        );

        $productFilterConfigsIndexedByKey = [];

        foreach ($batchLoadDataIndexedByKey as $key => $batchLoadData) {
            $productFilterConfigIdsData = $productFilterConfigIdsDataIndexedByKey[$key];
            $category = $categoriesIndexedByKey[$key] ?? null;

            $productFilterConfigsIndexedByKey[$key] = $this->create(
                $category === null ? [] : $this->getSortedParameterFilterChoicesForCategory(
                    $parameterFilterChoicesIndexedByKey[$key],
                    $sortedParameterIdsIndexedByCategoryId[$category->getId()],
                ),
                $this->pickByIds($flagsIndexedById, $productFilterConfigIdsData->getFlagIds()),
                $this->getBrandChoices($batchLoadData, $brandsIndexedById, $productFilterConfigIdsData),
                $productFilterConfigIdsData->getPriceRange(),
            );
        }

        return $productFilterConfigsIndexedByKey;
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\Brand\Brand> $brandsIndexedById
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    protected function getBrandChoices(
        ProductFilterBatchLoadData $batchLoadData,
        array $brandsIndexedById,
        ProductFilterConfigIdsData $productFilterConfigIdsData,
    ): array {
        if ($batchLoadData->getEntity() instanceof Brand) {
            return [];
        }

        return $this->pickByIds($brandsIndexedById, $productFilterConfigIdsData->getBrandIds());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData[] $batchLoadDataIndexedByKey
     * @return array<int|string, \Shopsys\FrameworkBundle\Model\Category\Category> indexed by the keys of category listings only
     */
    protected function getCategoriesIndexedByKey(array $batchLoadDataIndexedByKey): array
    {
        $categoriesIndexedByKey = [];

        foreach ($batchLoadDataIndexedByKey as $key => $batchLoadData) {
            $entity = $batchLoadData->getEntity();

            if ($entity instanceof Category) {
                $categoriesIndexedByKey[$key] = $entity;
            }
        }

        return $categoriesIndexedByKey;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData[] $productFilterConfigIdsDataIndexedByKey
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag> sorted by name, indexed by id
     */
    protected function getFlagsIndexedById(array $productFilterConfigIdsDataIndexedByKey, string $locale): array
    {
        $flagIds = $this->collectIds(
            $productFilterConfigIdsDataIndexedByKey,
            static fn (ProductFilterConfigIdsData $productFilterConfigIdsData): array => $productFilterConfigIdsData->getFlagIds(),
        );

        if ($flagIds === []) {
            return [];
        }

        return $this->indexById($this->flagFacade->getVisibleFlagsByIds($flagIds, $locale));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData[] $productFilterConfigIdsDataIndexedByKey
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Brand\Brand> sorted by name, indexed by id
     */
    protected function getBrandsIndexedById(array $productFilterConfigIdsDataIndexedByKey): array
    {
        $brandIds = $this->collectIds(
            $productFilterConfigIdsDataIndexedByKey,
            static fn (ProductFilterConfigIdsData $productFilterConfigIdsData): array => $productFilterConfigIdsData->getBrandIds(),
        );

        if ($brandIds === []) {
            return [];
        }

        return $this->indexById($this->brandFacade->getBrandsByIds($brandIds));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData[] $productFilterConfigIdsDataIndexedByKey
     * @param callable(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData): int[] $getIds
     * @return int[] unique ids of all listings
     */
    protected function collectIds(array $productFilterConfigIdsDataIndexedByKey, callable $getIds): array
    {
        $ids = [];

        foreach ($productFilterConfigIdsDataIndexedByKey as $productFilterConfigIdsData) {
            foreach ($getIds($productFilterConfigIdsData) as $id) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigIdsData[] $productFilterConfigIdsDataIndexedByKey
     * @param array<int|string, \Shopsys\FrameworkBundle\Model\Category\Category> $categoriesIndexedByKey
     * @return array<int|string, array<int, int[]>> parameter value ids by parameter id, indexed by the keys of category listings
     */
    protected function getParameterValueIdsByParameterIdIndexedByKey(
        array $productFilterConfigIdsDataIndexedByKey,
        array $categoriesIndexedByKey,
    ): array {
        $parameterValueIdsByParameterIdIndexedByKey = [];

        foreach (array_keys($categoriesIndexedByKey) as $key) {
            $parameterValueIdsByParameterIdIndexedByKey[$key] = $productFilterConfigIdsDataIndexedByKey[$key]->getParameterValueIdsByParameterId();
        }

        return $parameterValueIdsByParameterIdIndexedByKey;
    }

    /**
     * @template T of \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|\Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     * @param T[] $entities
     * @return array<int, T>
     */
    protected function indexById(array $entities): array
    {
        $entitiesIndexedById = [];

        foreach ($entities as $entity) {
            $entitiesIndexedById[$entity->getId()] = $entity;
        }

        return $entitiesIndexedById;
    }

    /**
     * @template T of \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|\Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     * @param array<int, T> $entitiesIndexedById
     * @param int[] $ids
     * @return T[]
     */
    protected function pickByIds(array $entitiesIndexedById, array $ids): array
    {
        $idsAsKeys = array_flip($ids);

        return array_values(array_intersect_key($entitiesIndexedById, $idsAsKeys));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[] $aggregatedParameterFilterChoices
     * @param int[] $sortedParameterIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ParameterFilterChoice[]
     */
    protected function getSortedParameterFilterChoicesForCategory(
        array $aggregatedParameterFilterChoices,
        array $sortedParameterIds,
    ): array {
        $aggregatedParametersFilterChoicesIndexedByParameterId = [];

        foreach ($aggregatedParameterFilterChoices as $aggregatedParameterFilterChoice) {
            $aggregatedParametersFilterChoicesIndexedByParameterId[$aggregatedParameterFilterChoice->getParameter()->getId()] = $aggregatedParameterFilterChoice;
        }

        $sortedParameterFilterChoices = [];

        foreach ($sortedParameterIds as $sortedParameterId) {
            if (!array_key_exists($sortedParameterId, $aggregatedParametersFilterChoicesIndexedByParameterId)) {
                continue;
            }
            $sortedParameterFilterChoices[] = $aggregatedParametersFilterChoicesIndexedByParameterId[$sortedParameterId];
        }

        return $sortedParameterFilterChoices;
    }
}

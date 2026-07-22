<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\AdditionalService;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class AdditionalServicesBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
        protected readonly AdditionalServiceQueryDtoFactory $additionalServiceQueryDtoFactory,
        protected readonly ProductFacade $productFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function loadByProductIds(array $productIds): Promise
    {
        $additionalServicesIndexedByProductId = $this->additionalServiceFacade->getEnabledIndexedByProductIds(
            $productIds,
            $this->domain->getId(),
        );

        $productsIndexedById = $this->getProductsWithAdditionalServicesIndexedById($additionalServicesIndexedByProductId);

        $additionalServiceQueryDtosOrderedByProductIds = [];

        foreach ($productIds as $productId) {
            $additionalServices = $additionalServicesIndexedByProductId[$productId] ?? [];

            if ($additionalServices === [] || !array_key_exists($productId, $productsIndexedById)) {
                $additionalServiceQueryDtosOrderedByProductIds[] = [];

                continue;
            }

            $additionalServiceQueryDtosOrderedByProductIds[] = $this->additionalServiceQueryDtoFactory->createMultiple(
                $additionalServices,
                $productsIndexedById[$productId],
            );
        }

        return $this->promiseAdapter->all($additionalServiceQueryDtosOrderedByProductIds);
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\AdditionalService\AdditionalServicesBatchLoadData[] $additionalServicesBatchLoadDataItems
     */
    public function loadByProductIdAndAdditionalServiceIds(array $additionalServicesBatchLoadDataItems): Promise
    {
        $allAdditionalServiceIds = [];
        $productIdsWithAdditionalServices = [];

        foreach ($additionalServicesBatchLoadDataItems as $additionalServicesBatchLoadData) {
            if ($additionalServicesBatchLoadData->getAdditionalServiceIds() === []) {
                continue;
            }

            $allAdditionalServiceIds = [...$allAdditionalServiceIds, ...$additionalServicesBatchLoadData->getAdditionalServiceIds()];
            $productIdsWithAdditionalServices[] = $additionalServicesBatchLoadData->getProductId();
        }

        $enabledAdditionalServicesIndexedById = $this->getEnabledAdditionalServicesIndexedById($allAdditionalServiceIds);
        $productsIndexedById = $this->getProductsIndexedById($productIdsWithAdditionalServices);

        $additionalServiceQueryDtosOrderedByBatchLoadData = [];

        foreach ($additionalServicesBatchLoadDataItems as $additionalServicesBatchLoadData) {
            $additionalServices = $this->filterAdditionalServicesOrderedByPosition(
                $enabledAdditionalServicesIndexedById,
                $additionalServicesBatchLoadData->getAdditionalServiceIds(),
            );
            $product = $productsIndexedById[$additionalServicesBatchLoadData->getProductId()] ?? null;

            if ($additionalServices === [] || $product === null) {
                $additionalServiceQueryDtosOrderedByBatchLoadData[] = [];

                continue;
            }

            $additionalServiceQueryDtosOrderedByBatchLoadData[] = $this->additionalServiceQueryDtoFactory->createMultiple(
                $additionalServices,
                $product,
            );
        }

        return $this->promiseAdapter->all($additionalServiceQueryDtosOrderedByBatchLoadData);
    }

    /**
     * @param int[] $additionalServiceIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService>
     */
    protected function getEnabledAdditionalServicesIndexedById(array $additionalServiceIds): array
    {
        if ($additionalServiceIds === []) {
            return [];
        }

        $additionalServicesIndexedById = [];

        $enabledAdditionalServices = $this->additionalServiceFacade->getEnabledByIds(
            array_values(array_unique($additionalServiceIds)),
            $this->domain->getId(),
        );

        foreach ($enabledAdditionalServices as $additionalService) {
            $additionalServicesIndexedById[$additionalService->getId()] = $additionalService;
        }

        return $additionalServicesIndexedById;
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService> $enabledAdditionalServicesIndexedById
     * @param int[] $additionalServiceIds
     * @return \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]
     */
    protected function filterAdditionalServicesOrderedByPosition(
        array $enabledAdditionalServicesIndexedById,
        array $additionalServiceIds,
    ): array {
        $additionalServices = [];

        foreach ($enabledAdditionalServicesIndexedById as $additionalServiceId => $additionalService) {
            if (in_array($additionalServiceId, $additionalServiceIds, true)) {
                $additionalServices[] = $additionalService;
            }
        }

        return $additionalServices;
    }

    /**
     * @param int[] $productIds
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product>
     */
    protected function getProductsIndexedById(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return $this->productFacade->getAllByIdsWithDomainsIndexedById($productIds);
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]> $additionalServicesIndexedByProductId
     * @return array<int, \Shopsys\FrameworkBundle\Model\Product\Product>
     */
    protected function getProductsWithAdditionalServicesIndexedById(array $additionalServicesIndexedByProductId): array
    {
        return $this->getProductsIndexedById(array_keys($additionalServicesIndexedByProductId));
    }
}

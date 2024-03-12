<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfigFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductRecalculationFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade $indexFacade
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry $indexRegistry
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationRepository $productRecalculationRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfigFacade $productExportScopeConfigFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     */
    public function __construct(
        protected readonly IndexFacade $indexFacade,
        protected readonly IndexRegistry $indexRegistry,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ProductRecalculationRepository $productRecalculationRepository,
        protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        protected readonly ProductExportScopeConfigFacade $productExportScopeConfigFacade,
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
    ) {
    }

    /**
     * @param int[] $productIds
     * @param array<int, string[]> $exportScopesIndexedByProductId
     */
    public function recalculate(array $productIds, array $exportScopesIndexedByProductId): void
    {
        foreach ($productIds as $productId) {
            $idsToRecalculate = $this->productRecalculationRepository->getIdsToRecalculate([$productId]);
            $this->recalculateWithScope($idsToRecalculate, $exportScopesIndexedByProductId[$productId] ?? []);
        }
    }

    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     */
    protected function recalculateWithScope(array $productIds, array $exportScopes): void
    {
        $shouldRecalculateVisibility = $this->productExportScopeConfigFacade->shouldRecalculateVisibility($exportScopes);

        if ($shouldRecalculateVisibility) {
            $this->productVisibilityFacade->calculateProductVisibilityForIds($productIds);
        }

        $shouldRecalculateSellingDenied = $this->productExportScopeConfigFacade->shouldRecalculateSellingDenied($exportScopes);

        if ($shouldRecalculateSellingDenied) {
            $this->productSellingDeniedRecalculator->calculateSellingDeniedForProductIds($productIds);
        }

        $fields = $this->productExportScopeConfigFacade->getExportFieldsByScopes($exportScopes);

        foreach ($this->domain->getAllIds() as $domainId) {
            $existingProductIds = $this->productElasticsearchProvider->getOnlyExistingProductsIds($productIds, $domainId);
            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ProductIndex::getName()),
                $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId),
                $existingProductIds,
                $fields,
            );

            if ($shouldRecalculateVisibility) {
                $nonExistingProductIds = array_diff($productIds, $existingProductIds);
                $this->indexFacade->exportIds(
                    $this->indexRegistry->getIndexByIndexName(ProductIndex::getName()),
                    $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId),
                    $nonExistingProductIds,
                );
            }
        }
    }
}

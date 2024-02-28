<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfigFacade;
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
    ) {
    }

    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     */
    public function recalculate(array $productIds, array $exportScopes): void
    {
        $idsToRecalculate = $this->productRecalculationRepository->getIdsToRecalculate($productIds);

        if ($this->productExportScopeConfigFacade->shouldRecalculateVisibility($exportScopes)) {
            $this->productVisibilityFacade->calculateProductVisibilityForIds($idsToRecalculate);
        }

        if ($this->productExportScopeConfigFacade->shouldRecalculateSellingDenied($exportScopes)) {
            $this->productSellingDeniedRecalculator->calculateSellingDeniedForProductIds($idsToRecalculate);
        }

        $fields = $this->productExportScopeConfigFacade->getExportFieldsByScopes($exportScopes);

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ProductIndex::getName()),
                $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId),
                $idsToRecalculate,
                $fields,
            );
        }
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeRegistry;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
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
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeRegistry $exportScopeRegistry
     */
    public function __construct(
        protected readonly IndexFacade $indexFacade,
        protected readonly IndexRegistry $indexRegistry,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ProductRecalculationRepository $productRecalculationRepository,
        protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        protected readonly ExportScopeRegistry $exportScopeRegistry,
    ) {
    }

    /**
     * @param int[] $productIds
     * @param string[] $affectedPropertyNames
     */
    public function recalculate(array $productIds, array $affectedPropertyNames): void
    {
        d('recalculate');
        $idsToRecalculate = $this->productRecalculationRepository->getIdsToRecalculate($productIds);

        $scopes = $this->exportScopeRegistry->getScopesByPropertyNames($affectedPropertyNames);

        // TODO pokud mám přepočítávat viditelnost, tak bych měl pro ty produkty tady čeknout jejich viditelnost (spíše pomocí ověření existence v elasticu). Pokud je produkt skrytý, musím tady nastavit scopes jako prázdné pole, tedy říct tím, že se má exportovat všechno
        if ($this->shouldRecalculateVisibility($scopes)) {
            $this->productVisibilityFacade->calculateProductVisibilityForIds($idsToRecalculate);
        }

        if ($this->shouldRecalculateSellingDenied($scopes)) {
            $this->productSellingDeniedRecalculator->calculateSellingDeniedForProductIds($idsToRecalculate);
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ProductIndex::getName()),
                $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId),
                $idsToRecalculate,
                $this->getElasticsearchFields($scopes, $affectedPropertyNames),
            );
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[] $scopes
     * @return bool
     */
    protected function shouldRecalculateVisibility(array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (in_array(ProductExportPreconditionsEnum::VISIBILITY_RECALCULATION, $scope->getPreconditions(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[] $scopes
     * @return bool
     */
    protected function shouldRecalculateSellingDenied(array $scopes): bool
    {
        foreach ($scopes as $scope) {
            if (in_array(ProductExportPreconditionsEnum::SELLING_DENIED_RECALCULATION, $scope->getPreconditions(), true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\Scope\ExportScopeInterface[] $scopes
     */
    protected function getElasticsearchFields(array $scopes, array $entityFieldNames): array
    {
        $elasticsearchFields = [];
        foreach ($scopes as $scope) {
            foreach ($entityFieldNames as $entityFieldName) {
                $fields = $scope->getElasticFieldNamesIndexedByEntityFieldNames()[$entityFieldName] ?? [];
                $elasticsearchFields = [...$elasticsearchFields, ...$fields];
            }
        }
d($elasticsearchFields);
        return $elasticsearchFields;
    }
}

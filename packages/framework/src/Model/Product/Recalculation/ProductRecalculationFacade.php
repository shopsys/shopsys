<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Nette\Utils\Json;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfigFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\PromotionFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductRecalculationFacade
{
    protected const string KEY_PRODUCT_IDS = 'productIds';
    protected const string KEY_SCOPES = 'scopes';

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
        protected readonly ProductRecalculationDeduplicationFacade $productRecalculationDeduplicationFacade,
        protected readonly GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade,
        protected readonly PromotionFlagFacade $promotionFlagFacade,
    ) {
    }

    /**
     * @param int[] $productIds
     */
    public function recalculate(array $productIds, string $priority, ?LoggerInterface $logger = null): void
    {
        $exportScopesIndexedByProductId = $this->productRecalculationDeduplicationFacade->getScopesIndexedByProductId($productIds, $priority);

        foreach ($this->groupProductIdsWithSameScopes($exportScopesIndexedByProductId) as $productIdsWithScopes) {
            $idsToRecalculate = $this->productRecalculationRepository->getRelevantIdsToRecalculate(
                $productIdsWithScopes[self::KEY_PRODUCT_IDS],
            );

            $this->recalculateWithScope($idsToRecalculate, $productIdsWithScopes[self::KEY_SCOPES]);

            $logger?->info('Products recalculated', [
                'product_ids' => Json::encode($idsToRecalculate),
                'export_scopes' => Json::encode($productIdsWithScopes[self::KEY_SCOPES]),
                'priority' => $priority,
            ]);

            $this->productRecalculationDeduplicationFacade->delete($idsToRecalculate, $priority);
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

        if ($shouldRecalculateVisibility || $shouldRecalculateSellingDenied) {
            foreach ($productIds as $productId) {
                $this->giftFlagSynchronizerFacade->refreshForGiftProductId($productId);
            }
        }

        if ($this->productExportScopeConfigFacade->shouldRecalculateGiftFlags($exportScopes)) {
            foreach ($productIds as $productId) {
                $this->giftFlagSynchronizerFacade->recalculateGiftFlagForMainProductId($productId);
            }
        }

        foreach ($productIds as $productId) {
            $this->promotionFlagFacade->updatePromotionFlags($productId);
        }

        $fields = $this->productExportScopeConfigFacade->getExportFieldsByScopes($exportScopes);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productIdsExistingInElastic = $this->productElasticsearchProvider->getOnlyExistingProductsIds($productIds, $domainId);
            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ProductIndex::getName()),
                $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId),
                $productIdsExistingInElastic,
                $fields,
            );

            if (!$shouldRecalculateVisibility) {
                continue;
            }

            $productIdsNotExistingInElastic = array_diff($productIds, $productIdsExistingInElastic);

            if (count($productIdsNotExistingInElastic) === 0) {
                continue;
            }

            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ProductIndex::getName()),
                $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId),
                $productIdsNotExistingInElastic,
            );
        }
    }

    /**
     * @param array<int, string[]> $exportScopesIndexedByProductId
     * @return array<int, array{productIds: int[], scopes: string[]}>
     */
    protected function groupProductIdsWithSameScopes(array $exportScopesIndexedByProductId): array
    {
        $scopeToProductIds = [];
        $emptyKey = 'empty';

        foreach ($exportScopesIndexedByProductId as $productId => $scopes) {
            sort($scopes);
            $scopesKey = $scopes ? implode(',', $scopes) : $emptyKey;
            $scopeToProductIds[$scopesKey][] = $productId;
        }

        $result = [];

        foreach ($scopeToProductIds as $key => $productIds) {
            $scopes = $key === $emptyKey ? [] : explode(',', $key);
            $result[] = [
                self::KEY_PRODUCT_IDS => $productIds,
                self::KEY_SCOPES => $scopes,
            ];
        }

        return $result;
    }
}

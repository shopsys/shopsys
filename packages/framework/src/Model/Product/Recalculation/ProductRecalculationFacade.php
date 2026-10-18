<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Nette\Utils\Json;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexFacade;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRegistry;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexRepository;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfigFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\PromotionFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository;

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
        protected readonly IndexRepository $indexRepository,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
        protected readonly TopProductRepository $topProductRepository,
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
        $promotedProductsDomainIds = $this->getPromotedProductsCacheDomainIds($productIds, $exportScopes);

        foreach ($this->domain->getAllIds() as $domainId) {
            $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ExtendedClassNameResolver::resolve(ProductIndex::class)::getName(), $domainId);
            $productIdsExistingInElastic = $this->productElasticsearchProvider->getOnlyExistingProductsIds($productIds, $domainId);
            $this->indexFacade->exportIds(
                $this->indexRegistry->getIndexByIndexName(ExtendedClassNameResolver::resolve(ProductIndex::class)::getName()),
                $indexDefinition,
                $productIdsExistingInElastic,
                $fields,
            );

            if ($shouldRecalculateVisibility) {
                $productIdsNotExistingInElastic = array_diff($productIds, $productIdsExistingInElastic);

                if (count($productIdsNotExistingInElastic) > 0) {
                    $this->indexFacade->exportIds(
                        $this->indexRegistry->getIndexByIndexName(ExtendedClassNameResolver::resolve(ProductIndex::class)::getName()),
                        $indexDefinition,
                        $productIdsNotExistingInElastic,
                    );
                }
            }

            if (in_array($domainId, $promotedProductsDomainIds, true)) {
                // Cache misses must already see the exported product state in search results.
                $this->indexRepository->refreshIndex($indexDefinition->getIndexAlias());
            }
        }

        if ($promotedProductsDomainIds !== []) {
            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_QUERY_KEY_PART);
        }
    }

    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     * @return int[]
     */
    protected function getPromotedProductsCacheDomainIds(array $productIds, array $exportScopes): array
    {
        if (
            $productIds === [] ||
            !($this->productExportScopeConfigFacade->shouldRecalculateVisibility($exportScopes) ||
                $this->productExportScopeConfigFacade->shouldRecalculateSellingDenied($exportScopes) ||
                in_array(ProductExportScopeConfig::SCOPE_TOP_PRODUCT, $exportScopes, true))
        ) {
            return [];
        }

        if (
            in_array(ProductExportScopeConfig::SCOPE_TOP_PRODUCT, $exportScopes, true) ||
            $this->productRecalculationRepository->hasDeletedProducts($productIds)
        ) {
            // Removed products no longer retain their promotion domains, including when a failed export is retried.
            return $this->domain->getAllIds();
        }

        return $this->topProductRepository->getDomainIdsForProductIds($productIds);
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

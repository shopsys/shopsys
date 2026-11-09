<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class GiftFlagSynchronizerFacade
{
    public const string FLAG_GIFT_UUID = '3b709b97-604d-462d-b045-885f126e78d2';
    protected const int REFRESH_ALL_BATCH_SIZE = 500;

    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GiftPlanRepository $giftPlanRepository,
        protected readonly FlagFacade $flagFacade,
        protected readonly Domain $domain,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly GiftCartFacade $giftCartFacade,
        protected readonly InMemoryCache $inMemoryCache,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    public function refreshAllGiftPlans(): void
    {
        $mainProductIds = $this->giftPlanRepository->getAllMainProductIds();

        $mainProductIdsBatches = array_chunk($mainProductIds, static::REFRESH_ALL_BATCH_SIZE);

        foreach ($mainProductIdsBatches as $mainProductIdsBatch) {
            $this->recalculateGiftFlagForProducts(
                $this->productFacade->getAllRefreshedByIdsWithDomainsAndFlagsIndexedById($mainProductIdsBatch),
            );
            $this->em->clear();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $mainProducts
     */
    public function dispatchMainProductForGriftFlagRecalculation(array $mainProducts): void
    {
        $this->dispatchMainProductIdsForGiftFlagRecalculation(
            array_map(static fn (Product $mainProduct): int => $mainProduct->getId(), $mainProducts),
        );
    }

    /**
     * @param int[] $giftProductIds
     */
    public function refreshForGiftProductIds(array $giftProductIds): void
    {
        $this->dispatchMainProductIdsForGiftFlagRecalculation(
            $this->giftPlanRepository->getMainProductIdsByGiftProductIds($giftProductIds),
        );
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\Product> $productsIndexedById
     */
    public function recalculateGiftFlagForProducts(array $productsIndexedById): void
    {
        if ($productsIndexedById === []) {
            return;
        }

        $giftProductIdsIndexedByDomainIdAndMainProductId = $this->giftPlanRepository->getActiveGiftProductIdsIndexedByDomainIdAndMainProductId(
            array_keys($productsIndexedById),
        );
        $allGiftProductIds = [];

        foreach ($giftProductIdsIndexedByDomainIdAndMainProductId as $giftProductIdsIndexedByMainProductId) {
            $allGiftProductIds = array_merge($allGiftProductIds, ...array_values($giftProductIdsIndexedByMainProductId));
        }

        $giftProductsIndexedById = $allGiftProductIds === []
            ? []
            : $this->productFacade->getAllByIdsWithDomainsIndexedById($allGiftProductIds);
        $domainIds = $this->domain->getAllIds();
        $hasAnyGiftFlagChanged = false;

        foreach ($domainIds as $domainId) {
            $giftProductIdsIndexedByMainProductId = $giftProductIdsIndexedByDomainIdAndMainProductId[$domainId] ?? [];
            $sellableGiftProductIds = $this->getSellableGiftProductIdsOnDomain(
                $giftProductsIndexedById,
                array_merge(...array_values($giftProductIdsIndexedByMainProductId)),
                $domainId,
            );

            foreach ($productsIndexedById as $productId => $product) {
                $shouldHaveGiftFlag = $this->shouldHaveGiftFlag(
                    $giftProductIdsIndexedByMainProductId[$productId] ?? [],
                    $sellableGiftProductIds,
                );
                $hasGiftFlagChanged = $this->updateGiftFlagOnDomain($product, $domainId, $shouldHaveGiftFlag);
                $hasAnyGiftFlagChanged = $hasAnyGiftFlagChanged || $hasGiftFlagChanged;
            }
        }

        if ($hasAnyGiftFlagChanged) {
            $this->em->flush();
        }
    }

    /**
     * @param int[] $mainProductIds
     */
    protected function dispatchMainProductIdsForGiftFlagRecalculation(array $mainProductIds): void
    {
        if ($mainProductIds === []) {
            return;
        }

        $this->productRecalculationDispatcher->dispatchProductIds(
            $mainProductIds,
            ProductRecalculationPriorityEnum::REGULAR,
            [ProductExportScopeConfig::SCOPE_GIFT_FLAGS],
        );
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\Product> $giftProductsIndexedById
     * @param int[] $giftProductIds
     * @return int[]
     */
    protected function getSellableGiftProductIdsOnDomain(
        array $giftProductsIndexedById,
        array $giftProductIds,
        int $domainId,
    ): array {
        if ($giftProductIds === []) {
            return [];
        }

        $giftProducts = array_map(
            static fn (int $giftProductId): Product => $giftProductsIndexedById[$giftProductId],
            array_values(array_unique($giftProductIds)),
        );

        return $this->giftCartFacade->getSellableGiftProductIds(
            $giftProducts,
            $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId),
            1,
            $domainId,
        );
    }

    /**
     * @param int[] $giftProductIdsOfMainProduct
     * @param int[] $sellableGiftProductIds
     */
    protected function shouldHaveGiftFlag(array $giftProductIdsOfMainProduct, array $sellableGiftProductIds): bool
    {
        return array_intersect($giftProductIdsOfMainProduct, $sellableGiftProductIds) !== [];
    }

    protected function updateGiftFlagOnDomain(Product $mainProduct, int $domainId, bool $shouldHaveFlag): bool
    {
        $flags = $mainProduct->getFlags($domainId);

        $giftFlag = $this->getGiftFlag();

        $hasGiftFlag = false;

        foreach ($flags as $flag) {
            if ($flag->getId() === $giftFlag->getId()) {
                $hasGiftFlag = true;

                break;
            }
        }

        $changed = false;

        if ($shouldHaveFlag && !$hasGiftFlag) {
            $flags[] = $giftFlag;
            $mainProduct->setFlags([$domainId => $flags]);
            $changed = true;
        } elseif (!$shouldHaveFlag && $hasGiftFlag) {
            $filtered = [];

            foreach ($flags as $flag) {
                if ($flag->getId() !== $giftFlag->getId()) {
                    $filtered[] = $flag;
                }
            }

            $mainProduct->setFlags([$domainId => $filtered]);
            $changed = true;
        }

        return $changed;
    }

    protected function getGiftFlag(): Flag
    {
        return $this->inMemoryCache->getOrSaveValue('giftFlag', fn () => $this->flagFacade->getByUuid(
            self::FLAG_GIFT_UUID,
        ), self::FLAG_GIFT_UUID);
    }
}

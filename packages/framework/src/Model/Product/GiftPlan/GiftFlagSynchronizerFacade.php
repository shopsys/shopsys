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

        $giftProductIdsIndexedByDomainIdAndProductId = $this->giftPlanRepository->getActiveGiftProductIdsIndexedByDomainIdAndMainProductId(
            array_keys($productsIndexedById),
        );
        $allGiftProductIds = [];

        foreach ($giftProductIdsIndexedByDomainIdAndProductId as $giftProductIdsIndexedByProductId) {
            $allGiftProductIds = array_merge($allGiftProductIds, ...array_values($giftProductIdsIndexedByProductId));
        }

        $giftProductsIndexedById = $allGiftProductIds === []
            ? []
            : $this->productFacade->getAllByIdsWithDomainsIndexedById($allGiftProductIds);
        $domainIds = $this->domain->getAllIds();
        $hasAnyGiftFlagChanged = false;

        foreach ($domainIds as $domainId) {
            $giftProductIdsIndexedByProductId = $giftProductIdsIndexedByDomainIdAndProductId[$domainId] ?? [];
            $sellableGiftProductIds = $this->getSellableGiftProductIdsOnDomain(
                $giftProductsIndexedById,
                array_merge(...array_values($giftProductIdsIndexedByProductId)),
                $domainId,
            );

            foreach ($productsIndexedById as $productId => $product) {
                $shouldHaveGiftFlag = $this->shouldHaveGiftFlag(
                    $giftProductIdsIndexedByProductId[$productId] ?? [],
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
     * @param int[] $giftProductIdsOfProduct
     * @param int[] $sellableGiftProductIds
     */
    protected function shouldHaveGiftFlag(array $giftProductIdsOfProduct, array $sellableGiftProductIds): bool
    {
        return array_intersect($giftProductIdsOfProduct, $sellableGiftProductIds) !== [];
    }

    protected function updateGiftFlagOnDomain(Product $product, int $domainId, bool $shouldHaveFlag): bool
    {
        $flags = $product->getFlags($domainId);

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
            $product->setFlags([$domainId => $flags]);
            $changed = true;
        } elseif (!$shouldHaveFlag && $hasGiftFlag) {
            $filtered = [];

            foreach ($flags as $flag) {
                if ($flag->getId() !== $giftFlag->getId()) {
                    $filtered[] = $flag;
                }
            }

            $product->setFlags([$domainId => $filtered]);
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

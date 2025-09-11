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
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class GiftFlagSynchronizerFacade
{
    public const FLAG_GIFT_UUID = '3b709b97-604d-462d-b045-885f126e78d2';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanRepository $giftPlanRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade $giftCartFacade
     * @param \Shopsys\FrameworkBundle\Component\Cache\InMemoryCache $inMemoryCache
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GiftPlanRepository $giftPlanRepository,
        protected readonly FlagFacade $flagFacade,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly Domain $domain,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly PricingGroupSettingFacade $pricingGroupSettingFacade,
        protected readonly GiftCartFacade $giftCartFacade,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    public function refreshAllGiftPlans(): void
    {
        $giftPlans = $this->giftPlanRepository->findAll();

        foreach ($giftPlans as $giftPlan) {
            $this->refreshForGiftPlan($giftPlan);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlan $giftPlan
     */
    public function refreshForGiftPlan(GiftPlan $giftPlan): void
    {
        $domainId = $giftPlan->getDomainId();

        foreach ($giftPlan->getMainProducts() as $mainProduct) {
            $this->refreshForMainProductOnDomain($mainProduct, $domainId);
        }
    }

    /**
     * @param array $mainProducts
     * @param int $domainId
     */
    public function refreshForMainProductsAndDomainId(array $mainProducts, int $domainId): void
    {
        foreach ($mainProducts as $mainProduct) {
            $this->refreshForMainProductOnDomain($mainProduct, $domainId);
        }
    }

    /**
     * @param int $giftProductId
     */
    public function refreshForGiftProductId(int $giftProductId): void
    {
        $giftPlans = $this->giftPlanRepository->findByGiftProductId($giftProductId);

        foreach ($giftPlans as $giftPlan) {
            $this->refreshForGiftPlan($giftPlan);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param int $domainId
     */
    public function refreshForMainProductOnDomain(Product $mainProduct, int $domainId): void
    {
        $shouldHaveFlag = $this->shouldHaveGiftFlag($mainProduct, $domainId);

        $mainProductDomain = $mainProduct->getProductDomain($domainId);
        $flags = $mainProductDomain->getFlags();

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
            $mainProductDomain->setFlags($flags);
            $changed = true;
        } elseif (!$shouldHaveFlag && $hasGiftFlag) {
            $filtered = [];

            foreach ($flags as $flag) {
                if ($flag->getId() !== $giftFlag->getId()) {
                    $filtered[] = $flag;
                }
            }

            $mainProductDomain->setFlags($filtered);
            $changed = true;
        }

        if (!$changed) {
            return;
        }

        $this->em->flush();

        $this->productRecalculationDispatcher->dispatchSingleProductId(
            $mainProduct->getId(),
            ProductRecalculationPriorityEnum::REGULAR,
            [ProductExportScopeConfig::SCOPE_FLAGS],
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param int $domainId
     * @return bool
     */
    protected function shouldHaveGiftFlag(Product $mainProduct, int $domainId): bool
    {
        $activeGiftPlans = $this->giftPlanRepository->findActiveGiftPlansByMainProductAndDomainId($mainProduct, $domainId);
        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId($domainId);

        foreach ($activeGiftPlans as $activeGiftPlan) {
            $giftProduct = $activeGiftPlan->getGiftProduct();

            if ($this->giftCartFacade->isGiftProductSellable($giftProduct, $defaultPricingGroup, $domainId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    protected function getGiftFlag(): Flag
    {
        return $this->inMemoryCache->getOrSaveValue('giftFlag', fn () => $this->flagFacade->getByUuid(
            self::FLAG_GIFT_UUID,
        ), self::FLAG_GIFT_UUID);
    }
}

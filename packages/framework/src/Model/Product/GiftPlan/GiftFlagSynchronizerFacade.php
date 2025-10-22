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
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class GiftFlagSynchronizerFacade
{
    public const string FLAG_GIFT_UUID = '3b709b97-604d-462d-b045-885f126e78d2';

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
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
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
        protected readonly ProductFacade $productFacade,
    ) {
    }

    public function refreshAllGiftPlans(): void
    {
        $giftPlans = $this->giftPlanRepository->findAll();

        foreach ($giftPlans as $giftPlan) {
            foreach ($giftPlan->getMainProducts() as $mainProduct) {
                foreach ($this->domain->getAllIds() as $domainId) {
                    $this->recalculateGiftFlagForMainProductAndDomainId($mainProduct, $domainId);
                }
            }
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $mainProducts
     */
    public function dispatchMainProductForGriftFlagRecalculation(array $mainProducts): void
    {
        foreach ($mainProducts as $mainProduct) {
            $this->productRecalculationDispatcher->dispatchSingleProductId(
                $mainProduct->getId(),
                ProductRecalculationPriorityEnum::REGULAR,
                [ProductExportScopeConfig::SCOPE_GIFT_FLAGS],
            );
        }
    }

    /**
     * @param int $giftProductId
     */
    public function refreshForGiftProductId(int $giftProductId): void
    {
        $giftPlans = $this->giftPlanRepository->findByGiftProductId($giftProductId);

        foreach ($giftPlans as $giftPlan) {
            $this->dispatchMainProductForGriftFlagRecalculation($giftPlan->getMainProducts());
        }
    }

    /**
     * @param int $mainProductId
     */
    public function recalculateGiftFlagForMainProductId(int $mainProductId): void
    {
        $mainProduct = $this->productFacade->getById($mainProductId);

        foreach ($this->domain->getAllIds() as $domainId) {
            $this->recalculateGiftFlagForMainProductAndDomainId($mainProduct, $domainId);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $mainProduct
     * @param int $domainId
     */
    protected function recalculateGiftFlagForMainProductAndDomainId(Product $mainProduct, int $domainId): void
    {
        $shouldHaveFlag = $this->shouldHaveGiftFlag($mainProduct, $domainId);

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

        if (!$changed) {
            return;
        }

        $this->em->flush();
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

            if ($this->giftCartFacade->isGiftProductSellable($giftProduct, $defaultPricingGroup, 1, $domainId)) {
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

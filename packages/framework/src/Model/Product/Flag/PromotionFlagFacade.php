<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository;

class PromotionFlagFacade
{
    protected const string DEFAULT_PROMOTION_FLAG_COLOR = '#efd6ff';

    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly FlagDataFactory $flagDataFactory,
        protected readonly Domain $domain,
        protected readonly ProductPromotionXyRepository $productPromotionXyRepository,
        protected readonly ProductPromotionXyFactory $productPromotionXyFactory,
        protected readonly ProductPromotionXyDataFactory $productPromotionXyDataFactory,
        protected readonly ProductFacade $productFacade,
    ) {
    }

    public function updatePromotionFlags(int $productId): void
    {
        try {
            $product = $this->productFacade->getById($productId);
            $flagsByDomainId = [];

            foreach ($this->domain->getAllIds() as $domainId) {
                $flagsByDomainId[$domainId] = $this->getUpdatedFlagsOnDomain($product, $domainId);
            }

            if (!$this->haveFlagsChanged($product, $flagsByDomainId)) {
                return;
            }

            $product->setFlags($flagsByDomainId);
            $this->em->flush();
        } catch (ProductNotFoundException) {
            // Product has been deleted, nothing to update
        }
    }

    public function updatePromotionFlagsForAll(): void
    {
        foreach ($this->productPromotionXyRepository->getAllProductIdsWithPromotionXy() as $productId) {
            $this->updatePromotionFlags($productId);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getUpdatedFlagsOnDomain(Product $product, int $domainId): array
    {
        $currentFlags = $product->getFlags($domainId);
        $promotionXy = $product->getPromotionXy($domainId);
        $isSellableOnDomain = !$product->isCalculatedSellingDenied($domainId);

        $nonPromotionFlags = array_filter(
            $currentFlags,
            static fn (Flag $flag): bool => !$flag->hasPromotionXy(),
        );

        if ($promotionXy === null || !$isSellableOnDomain) {
            return array_values($nonPromotionFlags);
        }

        $buyQuantity = $promotionXy->getBuyQuantity();
        $freeQuantity = $promotionXy->getFreeQuantity();

        $flagForCurrentProductPromotion = $this->findOrCreatePromotionFlag($buyQuantity, $freeQuantity);

        return array_values([...$nonPromotionFlags, $flagForCurrentProductPromotion]);
    }

    protected function findOrCreatePromotionFlag(int $x, int $y): Flag
    {
        $flag = $this->productPromotionXyRepository->findFlagByQuantities($x, $y);

        if ($flag !== null) {
            return $flag;
        }

        $promotionXy = $this->productPromotionXyRepository->findPromotionXyByQuantities($x, $y);

        if ($promotionXy === null) {
            $promotionXyData = $this->productPromotionXyDataFactory->create();
            $promotionXyData->buyQuantity = $x;
            $promotionXyData->freeQuantity = $y;
            $promotionXy = $this->productPromotionXyFactory->create($promotionXyData);
            $this->em->persist($promotionXy);
            $this->em->flush();
        }

        $flagData = $this->flagDataFactory->create();
        $flagData->visible = true;
        $flagData->rgbColor = static::DEFAULT_PROMOTION_FLAG_COLOR;
        $flagData->promotionXy = $promotionXy;

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();
            $flagData->name[$locale] = $this->buildPromotionFlagName($x, $y, $locale);
        }

        $flag = $this->flagFacade->create($flagData);
        $this->em->flush();

        return $flag;
    }

    protected function buildPromotionFlagName(int $x, int $y, string $locale): string
    {
        return t(
            'Promotion {{ x }} + {{ y }} free',
            [
                '{{ x }}' => $x,
                '{{ y }}' => $y,
            ],
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            $locale,
        );
    }

    /**
     * @param array<int, array<\Shopsys\FrameworkBundle\Model\Product\Flag\Flag>> $newFlagsByDomainId
     */
    protected function haveFlagsChanged(Product $product, array $newFlagsByDomainId): bool
    {
        foreach ($newFlagsByDomainId as $domainId => $newFlags) {
            $currentFlags = $product->getFlags($domainId);

            if (count($currentFlags) !== count($newFlags)) {
                return true;
            }

            $currentFlagIds = array_map(static fn (Flag $flag): int => $flag->getId(), $currentFlags);
            $newFlagIds = array_map(static fn (Flag $flag): int => $flag->getId(), $newFlags);

            sort($currentFlagIds);
            sort($newFlagIds);

            if ($currentFlagIds !== $newFlagIds) {
                return true;
            }
        }

        return false;
    }
}

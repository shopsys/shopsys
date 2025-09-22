<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository;

class PromotionFlagFacade
{
    protected const string DEFAULT_PROMOTION_FLAG_COLOR = '#efd6ff';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory $flagDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository $productPromotionXyRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory $productPromotionXyFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory $productPromotionXyDataFactory
     */
    public function __construct(
        protected readonly FlagFacade $flagFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly FlagDataFactory $flagDataFactory,
        protected readonly Domain $domain,
        protected readonly ProductPromotionXyRepository $productPromotionXyRepository,
        protected readonly ProductPromotionXyFactory $productPromotionXyFactory,
        protected readonly ProductPromotionXyDataFactory $productPromotionXyDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    public function updatePromotionFlags(Product $product, ProductData $productData): void
    {
        if ($productData->promotionXyData === null) {
            return;
        }

        $isSellableOnDomain = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $isSellableOnDomain[$domainId] = $product->isCalculatedSellingDenied($domainId) === false;
        }

        $promotionXyData = $productData->promotionXyData;
        $promotionBuyQuantity = $promotionXyData->buyQuantity;
        $promotionFreeQuantity = $promotionXyData->freeQuantity;

        foreach ($this->domain->getAllIds() as $domainId) {
            $flags = $productData->flagsByDomainId[$domainId] ?? [];

            $promotionFlags = array_filter(
                $flags,
                fn (Flag $flag): bool => $flag->hasPromotionXy(),
            );

            if (count($promotionFlags) <= 0) {
                continue;
            }

            $promotionFlag = reset($promotionFlags);

            if (
                $isSellableOnDomain[$domainId] &&
                $promotionFlag->getPromotionXy()->getBuyQuantity() === $promotionBuyQuantity &&
                $promotionFlag->getPromotionXy()->getFreeQuantity() === $promotionFreeQuantity
            ) {
                return;
            }

            $productData->flagsByDomainId[$domainId] = array_values(array_filter(
                $flags,
                fn (Flag $flag): bool => !$flag->hasPromotionXy(),
            ));
        }

        if ($promotionBuyQuantity === null || $promotionFreeQuantity === null) {
            return;
        }

        $flag = $this->findOrCreatePromotionFlag($promotionBuyQuantity, $promotionFreeQuantity);

        foreach ($this->domain->getAllIds() as $domainId) {
            if ($isSellableOnDomain[$domainId] && !in_array($flag, $productData->flagsByDomainId[$domainId], true)) {
                $productData->flagsByDomainId[$domainId][] = $flag;
            }
        }

        $product->setFlags($productData->flagsByDomainId);
    }

    /**
     * @param int $x
     * @param int $y
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function findOrCreatePromotionFlag(int $x, int $y): Flag
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

    /**
     * @param int $x
     * @param int $y
     * @param string $locale
     * @return string
     */
    protected function buildPromotionFlagName(int $x, int $y, string $locale)
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
}

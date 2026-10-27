<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductPromotionXyRepository;

class PromotionFlagFacade
{
    protected const string DEFAULT_PROMOTION_FLAG_COLOR = '#efd6ff';
    protected const int UPDATE_ALL_BATCH_SIZE = 500;

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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function updatePromotionFlagsForProducts(array $products): void
    {
        $domainIds = $this->domain->getAllIds();
        $promotionFlagsIndexedByProductIdAndDomainId = $this->getPromotionFlagsIndexedByProductIdAndDomainId($products, $domainIds);
        $haveAnyFlagsChanged = false;

        foreach ($products as $product) {
            $flagsByDomainId = [];

            foreach ($domainIds as $domainId) {
                $flagsByDomainId[$domainId] = $this->getUpdatedFlagsOnDomain(
                    $product,
                    $domainId,
                    $promotionFlagsIndexedByProductIdAndDomainId[$product->getId()][$domainId],
                );
            }

            if (!$this->haveFlagsChanged($product, $flagsByDomainId)) {
                continue;
            }

            $product->setFlags($flagsByDomainId);
            $haveAnyFlagsChanged = true;
        }

        if ($haveAnyFlagsChanged) {
            $this->em->flush();
        }
    }

    public function updatePromotionFlagsForAll(): void
    {
        $productIdsWithPromotionXy = $this->productPromotionXyRepository->getAllProductIdsWithPromotionXy();

        $productIdsBatches = array_chunk($productIdsWithPromotionXy, static::UPDATE_ALL_BATCH_SIZE);

        foreach ($productIdsBatches as $productIdsBatch) {
            $this->updatePromotionFlagsForProducts(
                $this->productFacade->getAllRefreshedByIdsWithDomainsAndFlagsIndexedById($productIdsBatch),
            );
            $this->em->clear();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param int[] $domainIds
     * @return array<int, array<int, \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|null>>
     */
    protected function getPromotionFlagsIndexedByProductIdAndDomainId(array $products, array $domainIds): array
    {
        $promotionFlagsIndexedByQuantities = [];
        $promotionFlagsIndexedByProductIdAndDomainId = [];

        foreach ($products as $product) {
            foreach ($domainIds as $domainId) {
                $promotionXy = $this->findApplicablePromotionXy($product, $domainId);

                if ($promotionXy === null) {
                    $promotionFlagsIndexedByProductIdAndDomainId[$product->getId()][$domainId] = null;

                    continue;
                }

                $promotionQuantitiesKey = $promotionXy->getBuyQuantity() . '+' . $promotionXy->getFreeQuantity();

                if (!array_key_exists($promotionQuantitiesKey, $promotionFlagsIndexedByQuantities)) {
                    $promotionFlagsIndexedByQuantities[$promotionQuantitiesKey] = $this->findOrCreatePromotionFlag(
                        $promotionXy->getBuyQuantity(),
                        $promotionXy->getFreeQuantity(),
                    );
                }

                $promotionFlagsIndexedByProductIdAndDomainId[$product->getId()][$domainId] = $promotionFlagsIndexedByQuantities[$promotionQuantitiesKey];
            }
        }

        return $promotionFlagsIndexedByProductIdAndDomainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getUpdatedFlagsOnDomain(Product $product, int $domainId, ?Flag $promotionFlag): array
    {
        $nonPromotionFlags = array_filter(
            $product->getFlags($domainId),
            static fn (Flag $flag): bool => !$flag->hasPromotionXy(),
        );

        if ($promotionFlag === null) {
            return array_values($nonPromotionFlags);
        }

        return array_values([...$nonPromotionFlags, $promotionFlag]);
    }

    protected function findApplicablePromotionXy(Product $product, int $domainId): ?ProductPromotionXy
    {
        if ($product->isCalculatedSellingDenied($domainId)) {
            return null;
        }

        return $product->getPromotionXy($domainId);
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Flag;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

class PromotionFlagFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagRepository $flagRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagDataFactory $flagDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly FlagRepository $flagRepository,
        protected readonly FlagFacade $flagFacade,
        protected readonly EntityManagerInterface $em,
        protected readonly FlagDataFactory $flagDataFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductData $productData
     */
    public function updatePromotionFlagsInProductData(ProductData $productData): void
    {
        $hasPromotionFlag = false;

        foreach ($this->domain->getAllIds() as $domainId) {
            foreach ($productData->flagsByDomainId[$domainId] as $key => $flag) {
                if ($flag->getPromotionX() !== null && $flag->getPromotionY() !== null) {
                    $promotionFlag = $flag;

                    if (
                        $promotionFlag->getPromotionX() === $productData->promotionX &&
                        $promotionFlag->getPromotionY() === $productData->promotionY) {
                        $hasPromotionFlag = true;

                        continue;
                    }

                    unset($productData->flagsByDomainId[$domainId][$key]);
                }
            }
        }

        if ($productData->promotionX === null || $productData->promotionY === null || $hasPromotionFlag) {
            return;
        }

        $flag = $this->findOrCreatePromotionFlag((int)$productData->promotionX, (int)$productData->promotionY);

        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->flagsByDomainId[$domainId][] = $flag;
        }
    }

    /**
     * @param int $x
     * @param int $y
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function findOrCreatePromotionFlag(int $x, int $y): Flag
    {
        $flag = $this->flagRepository->findOneByPromotion($x, $y);

        if ($flag !== null) {
            return $flag;
        }

        $flagData = $this->flagDataFactory->create();
        $flagData->visible = true;
        $flagData->rgbColor = '#efd6ff';
        $flagData->promotionX = $x;
        $flagData->promotionY = $y;

        foreach ($this->domain->getAll() as $domainConfig) {
            $locale = $domainConfig->getLocale();

            $flagData->name[$locale] = t(
                'Buy %x% get %y% free',
                [
                    '%x%' => $x,
                    '%y%' => $y,
                ],
                Translator::DEFAULT_TRANSLATION_DOMAIN,
                $locale,
            );
        }

        $flag = $this->flagFacade->create($flagData);
        $this->em->flush();

        return $flag;
    }
}

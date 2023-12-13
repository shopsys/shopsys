<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    public function getIdsToRecalculate(array $productIds): array
    {
        $mainVariantsIds = $this->getMainVariantIdsOfVariants($productIds);

        $productIdsWithAddedMainVariantIds = [...$productIds, ...$mainVariantsIds];

        $regularProductOrMainVariantIds = $this->dropVariantIdsFromProductIds($productIdsWithAddedMainVariantIds);
        $variantIds = $this->getVariantIds($productIdsWithAddedMainVariantIds);

        return [...$variantIds, ...$regularProductOrMainVariantIds];
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    protected function getMainVariantIdsOfVariants(array $productIds): array
    {
        $result = $this->em->createQuery('SELECT IDENTITY(p.mainVariant) as id FROM ' . Product::class . ' p WHERE p.id IN (:productIds) AND p.variantType = :variantTypeVariant')
            ->setParameter('productIds', $productIds)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT)
            ->getResult();

        return array_column($result, 'id');
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    protected function dropVariantIdsFromProductIds(array $productIds): array
    {
        $result = $this->em->createQuery('SELECT p.id FROM ' . Product::class . ' p WHERE p.id IN (:productIds) AND p.variantType != :variantTypeVariant')
            ->setParameter('productIds', $productIds)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT)
            ->getResult();

        return array_column($result, 'id');
    }

    /**
     * @param int[] $mainVariantsIds
     * @return int[]
     */
    protected function getVariantIds(array $mainVariantsIds): array
    {
        $result = $this->em->createQuery('SELECT p.id FROM ' . Product::class . ' p WHERE p.mainVariant IN (:productIds) AND p.variantType = :variantTypeVariant')
            ->setParameter('productIds', $mainVariantsIds)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT)
            ->getResult();

        return array_column($result, 'id');
    }
}

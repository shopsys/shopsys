<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    public function replaceVariantIdsWithMainVariantIds(array $productIds): array
    {
        $ids = $this->em
            ->createQuery(
                'SELECT DISTINCT 
                CASE
                    WHEN p.variantType = :variantTypeVariant THEN IDENTITY(p.mainVariant) 
                    ELSE p.id
                END AS resolved_id
                FROM ' . Product::class . ' p 
                WHERE p.id IN (:productIds)',
            )
            ->setParameter('productIds', $productIds)
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT)
            ->getArrayResult();

        return array_column($ids, 'resolved_id');
    }

    /**
     * @param int[] $inputProductIds
     * @return int[]
     */
    public function getRelevantIdsToRecalculate(array $inputProductIds): array
    {
        $ids = $this->em
            ->createQuery('SELECT p.id FROM ' . Product::class . ' p WHERE p.mainVariant IN (:productIds) OR p.id IN (:productIds)')
            ->setParameter('productIds', $inputProductIds)
            ->getArrayResult();

        $ids = array_column($ids, 'id');

        return array_unique([...$ids, ...$inputProductIds]);
    }
}

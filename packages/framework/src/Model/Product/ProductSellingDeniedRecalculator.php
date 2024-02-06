<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class ProductSellingDeniedRecalculator
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @param int[] $productIds
     */
    public function calculateSellingDeniedForProductIds(array $productIds): void
    {
        $this->calculate($productIds);
    }

    public function calculateSellingDeniedForAll(): void
    {
        $this->calculate();
    }

    /**
     * @param int[] $productIds
     */
    protected function calculate(array $productIds = []): void
    {
        $this->calculateIndependent($productIds);
        $this->propagateMainVariantSellingDeniedToVariants($productIds);
        $this->propagateVariantsSellingDeniedToMainVariant($productIds);
    }

    /**
     * @param int[] $productIds
     */
    protected function calculateIndependent(array $productIds): void
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'p.sellingDenied');

        if (count($productIds) > 0) {
            $qb->andWhere('p IN (:productIds)')->setParameter('productIds', $productIds);
        }
        $qb->getQuery()->execute();
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateMainVariantSellingDeniedToVariants(array $productIds): void
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'TRUE')
            ->andWhere('p.variantType = :variantTypeVariant')
            ->andWhere('p.calculatedSellingDenied = FALSE')
            ->andWhere(
                'EXISTS (
                    SELECT 1
                    FROM ' . Product::class . ' m
                    WHERE m = p.mainVariant
                        AND m.calculatedSellingDenied = TRUE
                )',
            )
            ->setParameter('variantTypeVariant', Product::VARIANT_TYPE_VARIANT);

        if (count($productIds) > 0) {
            $qb->andWhere('p IN (:productIds)')->setParameter('productIds', $productIds);
        }
        $qb->getQuery()->execute();
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateVariantsSellingDeniedToMainVariant(array $productIds): void
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'TRUE')
            ->andWhere('p.variantType = :variantTypeMain')
            ->andWhere('p.calculatedSellingDenied = FALSE')
            ->andWhere(
                'NOT EXISTS (
                    SELECT 1
                    FROM ' . Product::class . ' v
                    WHERE v.mainVariant = p
                        AND v.calculatedSellingDenied = FALSE
                )',
            )
            ->setParameter('variantTypeMain', Product::VARIANT_TYPE_MAIN);

        if (count($productIds) > 0) {
            $qb->andWhere('p IN (:productIds)')->setParameter('productIds', $productIds);
        }
        $qb->getQuery()->execute();
    }
}

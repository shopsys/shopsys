<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ProductSellingDeniedRecalculator
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly Domain $domain,
    ) {
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
        $this->propagateCalculatedSaleExclusionToCalculatedSellingDenied($productIds);
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

        $this->calculatePerDomain($productIds);
    }

    /**
     * @param int[] $productIds
     */
    protected function calculatePerDomain(array $productIds): void
    {
        $query = 'UPDATE product_domains AS pd
            SET calculated_sale_exclusion = CASE
                    WHEN (
                        p.calculated_selling_denied = TRUE
                        OR
                        pd.domain_hidden = TRUE
                        OR (
                            p.variant_type != :variantTypeMain
                            AND
                            NOT EXISTS(
                                SELECT 1
                                FROM product_stocks as ps
                                JOIN stocks as s ON s.id = ps.stock_id
                                JOIN stock_domains sd ON s.id = sd.stock_id AND sd.domain_id = :domainId
                                WHERE ps.product_id = p.id AND sd.is_enabled = TRUE
                                HAVING SUM(ps.product_quantity) > 0
                            )
                        )
                        OR (
                            pd.sale_exclusion = TRUE
                            AND                        
                            p.variant_type = :variantTypeMain
                        )
                    )
                    THEN TRUE
                    ELSE FALSE
                END
            FROM products AS p
            WHERE p.id = pd.product_id
                AND pd.domain_id = :domainId
            ' . (count($productIds) > 0 ? ' AND p.id IN (:productIds)' : '');

        $params = [];
        $params['productIds'] = $productIds;
        $params['variantTypeMain'] = Product::VARIANT_TYPE_MAIN;

        foreach ($this->domain->getAll() as $domain) {
            $params['domainId'] = $domain->getId();

            $this->em->getConnection()->executeStatement(
                $query,
                $params,
                [
                    'productIds' => ArrayParameterType::INTEGER,
                    'variantTypeMain' => Types::STRING,
                    'domainId' => Types::INTEGER,
                ],
            );
        }
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateCalculatedSaleExclusionToCalculatedSellingDenied(array $productIds): void
    {
        $query = 'UPDATE products as p
                SET calculated_selling_denied = TRUE
                WHERE p.calculated_selling_denied = FALSE
                AND NOT EXISTS (
                    SELECT 1
                    FROM product_domains as pd
                    WHERE pd.product_id = p.id
                        AND pd.calculated_sale_exclusion = FALSE
                )
            ' . (count($productIds) > 0 ? ' AND p.id IN (:productIds)' : '');

        $this->em->getConnection()->executeStatement(
            $query,
            ['productIds' => $productIds],
            ['productIds' => ArrayParameterType::INTEGER],
        );
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

        $this->propagateMainVariantSellingDeniedToVariantsCalculatedSaleExclusion($productIds);
        $this->propagateMainVariantCalculateSaleExclusionToVariantsCalculatedSaleExclusion($productIds);
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateMainVariantSellingDeniedToVariantsCalculatedSaleExclusion(array $productIds): void
    {
        $query = 'UPDATE product_domains as pd
                SET calculated_sale_exclusion = TRUE
                FROM products as p
                JOIN products as m ON p.main_variant_id = m.id
                WHERE m.variant_type = :variantTypeMain
                AND pd.product_id = p.id
                AND pd.calculated_sale_exclusion = FALSE
                AND p.calculated_selling_denied = TRUE
            ' . (count($productIds) > 0 ? ' AND m.id IN (:productIds)' : '');

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => ArrayParameterType::INTEGER,
                'variantTypeMain' => Types::STRING,
            ],
        );
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateMainVariantCalculateSaleExclusionToVariantsCalculatedSaleExclusion(
        array $productIds,
    ): void {
        $query = 'UPDATE product_domains as pd
                SET calculated_sale_exclusion = TRUE
                FROM products as p
                JOIN products as m ON p.main_variant_id = m.id
                JOIN product_domains as pdm ON pdm.product_id = m.id 
                WHERE m.variant_type = :variantTypeMain
                AND pd.product_id = p.id
                AND pdm.domain_id = pd.domain_id
                AND pd.calculated_sale_exclusion = FALSE
                AND pdm.calculated_sale_exclusion = TRUE
            ' . (count($productIds) > 0 ? ' AND m.id IN (:productIds)' : '');

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => ArrayParameterType::INTEGER,
                'variantTypeMain' => Types::STRING,
            ],
        );
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

        $this->propagateVariantsSaleExclusionToMainVariantCalculateSaleExclusion($productIds);
        $this->propagateVariantsSaleExclusionToMainVariantCalculateSellingDenied($productIds);
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateVariantsSaleExclusionToMainVariantCalculateSaleExclusion(array $productIds): void
    {
        $query = 'UPDATE product_domains as pd
                SET calculated_sale_exclusion = TRUE
                FROM products as p 
                WHERE p.variant_type = :variantTypeMain
                AND pd.product_id = p.id
                AND pd.calculated_sale_exclusion = FALSE
                AND NOT EXISTS (
                    SELECT 1
                    FROM products as v
                    JOIN product_visibilities pv ON pv.product_id = v.id AND pv.visible = TRUE
                    JOIN product_domains as pdv ON pdv.product_id = v.id AND pdv.domain_id = pd.domain_id
                    WHERE v.main_variant_id = p.id
                        AND pdv.calculated_sale_exclusion = FALSE
                )
            ' . (count($productIds) > 0 ? ' AND p.id IN (:productIds)' : '');

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => ArrayParameterType::INTEGER,
                'variantTypeMain' => Types::STRING,
            ],
        );
    }

    /**
     * @param int[] $productIds
     */
    protected function propagateVariantsSaleExclusionToMainVariantCalculateSellingDenied(array $productIds): void
    {
        $query = 'UPDATE products as p
                SET calculated_selling_denied = TRUE
                WHERE p.variant_type = :variantTypeMain
                AND p.calculated_selling_denied = FALSE
                AND NOT EXISTS (
                    SELECT 1
                    FROM products as v
                    JOIN product_visibilities pv ON pv.product_id = v.id AND pv.visible = TRUE
                    JOIN product_domains as pdv ON pdv.product_id = v.id
                    WHERE v.main_variant_id = p.id
                        AND pdv.calculated_sale_exclusion = FALSE
                )
            ' . (count($productIds) > 0 ? ' AND p.id IN (:productIds)' : '');

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => ArrayParameterType::INTEGER,
                'variantTypeMain' => Types::STRING,
            ],
        );
    }
}

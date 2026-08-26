<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;

class ProductSellingDeniedRecalculator
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
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
        $this->calculatePerDomain($productIds);
        $this->propagateMainVariantSellingDeniedToVariants($productIds);
        $this->propagateVariantsSellingDeniedToMainVariant($productIds);
    }

    /**
     * @param int[] $productIds
     */
    protected function calculatePerDomain(array $productIds): void
    {
        $query = 'UPDATE product_domains AS pd
            SET calculated_selling_denied = CASE
                WHEN (
                    p.selling_denied = TRUE
                    OR
                    pd.selling_denied = TRUE
                )
                THEN TRUE
                ELSE FALSE
            END
            FROM products AS p
            WHERE p.id = pd.product_id
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
        $query = 'UPDATE product_domains as pd
                SET calculated_selling_denied = TRUE
                FROM products as p
                JOIN products as m ON p.main_variant_id = m.id
                JOIN product_domains as pdm ON pdm.product_id = m.id 
                WHERE m.variant_type = :variantTypeMain
                    AND pd.product_id = p.id
                    AND pdm.domain_id = pd.domain_id
                    AND pd.calculated_selling_denied = FALSE
                    AND pdm.calculated_selling_denied = TRUE
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
        $query = 'UPDATE product_domains as pd
                SET calculated_selling_denied = TRUE
                FROM products as p 
                WHERE p.variant_type = :variantTypeMain
                AND pd.product_id = p.id
                AND pd.calculated_selling_denied = FALSE
                AND NOT EXISTS (
                    SELECT 1
                    FROM products as v
                    JOIN product_domains as pdv ON pdv.product_id = v.id AND pdv.domain_id = pd.domain_id
                    WHERE v.main_variant_id = p.id
                        AND pdv.calculated_selling_denied = FALSE
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

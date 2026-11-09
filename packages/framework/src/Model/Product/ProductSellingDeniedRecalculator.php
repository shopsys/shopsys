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
        if ($productIds === []) {
            return;
        }

        $this->recalculateSellingDenied($productIds);
    }

    public function calculateSellingDeniedForAll(): void
    {
        $this->recalculateSellingDenied(null);
    }

    /**
     * @param int[]|null $productIds
     */
    protected function recalculateSellingDenied(?array $productIds): void
    {
        $parameters = [
            ...$this->getIndependentSellingDeniedParameters(),
            'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            'variantTypeVariant' => Product::VARIANT_TYPE_VARIANT,
        ];
        $parameterTypes = [
            ...$this->getIndependentSellingDeniedParameterTypes(),
            'variantTypeMain' => Types::STRING,
            'variantTypeVariant' => Types::STRING,
        ];

        if ($productIds !== null) {
            $parameters['productIds'] = $productIds;
            $parameterTypes['productIds'] = ArrayParameterType::INTEGER;
        }

        $this->em->getConnection()->executeStatement(
            $this->getSellingDeniedRecalculationSql($productIds !== null),
            $parameters,
            $parameterTypes,
        );
    }

    protected function getSellingDeniedRecalculationSql(bool $isRestrictedToProductIds): string
    {
        $productIdsCondition = $isRestrictedToProductIds ? 'WHERE pd.product_id IN (:productIds)' : '';

        return 'WITH scoped_product_domains AS (
            SELECT
                pd.product_id,
                pd.domain_id,
                p.variant_type,
                p.main_variant_id,
                COALESCE((' . $this->getIndependentSellingDeniedConditionSql() . '), FALSE) AS independently_selling_denied
            FROM product_domains AS pd
            JOIN products AS p ON p.id = pd.product_id
            ' . $productIdsCondition . '
        ),
        variant_family_product_domains AS (
            SELECT
                spd.*,
                BOOL_OR(spd.independently_selling_denied) FILTER (WHERE spd.variant_type = :variantTypeMain) OVER variant_family AS main_variant_independently_selling_denied,
                BOOL_AND(spd.independently_selling_denied) FILTER (WHERE spd.variant_type = :variantTypeVariant) OVER variant_family AS all_variants_independently_selling_denied
            FROM scoped_product_domains AS spd
            WINDOW variant_family AS (PARTITION BY COALESCE(spd.main_variant_id, spd.product_id), spd.domain_id)
        ),
        calculated_product_domains AS (
            SELECT
                vfpd.product_id,
                vfpd.domain_id,
                CASE
                    WHEN vfpd.variant_type = :variantTypeVariant THEN ' . $this->getVariantSellingDeniedConditionSql() . '
                    WHEN vfpd.variant_type = :variantTypeMain THEN ' . $this->getMainVariantSellingDeniedConditionSql() . '
                    ELSE vfpd.independently_selling_denied
                END AS calculated_selling_denied
            FROM variant_family_product_domains AS vfpd
        )
        UPDATE product_domains AS pd
        SET calculated_selling_denied = cpd.calculated_selling_denied
        FROM calculated_product_domains AS cpd
        WHERE pd.product_id = cpd.product_id
            AND pd.domain_id = cpd.domain_id
            AND pd.calculated_selling_denied IS DISTINCT FROM cpd.calculated_selling_denied';
    }

    protected function getVariantSellingDeniedConditionSql(): string
    {
        return '(vfpd.independently_selling_denied OR COALESCE(vfpd.main_variant_independently_selling_denied, FALSE))';
    }

    protected function getMainVariantSellingDeniedConditionSql(): string
    {
        return '(vfpd.independently_selling_denied OR COALESCE(vfpd.all_variants_independently_selling_denied, TRUE))';
    }

    protected function getIndependentSellingDeniedConditionSql(): string
    {
        return '(p.selling_denied = TRUE OR pd.selling_denied = TRUE)';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getIndependentSellingDeniedParameters(): array
    {
        return [];
    }

    /**
     * @return array<string, string>
     */
    protected function getIndependentSellingDeniedParameterTypes(): array
    {
        return [];
    }
}

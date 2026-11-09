<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\DBAL\Types\Types;

class ProductSellingDeniedRecalculator
{
    public function __construct(
        protected readonly ProductVariantFamilyRecalculationRepository $productVariantFamilyRecalculationRepository,
    ) {
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    public function calculateSellingDeniedForProductIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return $this->recalculateSellingDenied($productIds);
    }

    public function calculateSellingDeniedForAll(): void
    {
        $this->recalculateSellingDenied(null);
    }

    /**
     * @param int[]|null $productIds
     * @return int[]
     */
    protected function recalculateSellingDenied(?array $productIds): array
    {
        return $this->productVariantFamilyRecalculationRepository->executeRecalculationAndGetChangedProductIds(
            $this->getSellingDeniedRecalculationSql($productIds !== null),
            [
                ...$this->getIndependentSellingDeniedParameters(),
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
                'variantTypeVariant' => Product::VARIANT_TYPE_VARIANT,
            ],
            [
                ...$this->getIndependentSellingDeniedParameterTypes(),
                'variantTypeMain' => Types::STRING,
                'variantTypeVariant' => Types::STRING,
            ],
            $productIds,
        );
    }

    protected function getSellingDeniedRecalculationSql(bool $isRestrictedToProductIds): string
    {
        return 'WITH ' . $this->productVariantFamilyRecalculationRepository->getRequestedVariantFamilyRestrictionCteSql($isRestrictedToProductIds) . '
        scoped_product_domains AS (
            SELECT
                pd.product_id,
                pd.domain_id,
                p.variant_type,
                p.main_variant_id,
                COALESCE((' . $this->getIndependentSellingDeniedConditionSql() . '), FALSE) AS independently_selling_denied
            FROM product_domains AS pd
            JOIN products AS p ON p.id = pd.product_id
            ' . $this->productVariantFamilyRecalculationRepository->getRequestedVariantFamilyRestrictionWhereSql('pd.product_id', $isRestrictedToProductIds) . '
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
                    WHEN vfpd.variant_type = :variantTypeVariant THEN
                        vfpd.independently_selling_denied
                        OR COALESCE(vfpd.main_variant_independently_selling_denied, FALSE)
                    WHEN vfpd.variant_type = :variantTypeMain THEN
                        vfpd.independently_selling_denied
                        OR COALESCE(vfpd.all_variants_independently_selling_denied, TRUE)
                    ELSE vfpd.independently_selling_denied
                END AS calculated_selling_denied
            FROM variant_family_product_domains AS vfpd
        ),
        updated_product_domains AS (
            UPDATE product_domains AS pd
            SET calculated_selling_denied = cpd.calculated_selling_denied
            FROM calculated_product_domains AS cpd
            WHERE pd.product_id = cpd.product_id
                AND pd.domain_id = cpd.domain_id
                AND pd.calculated_selling_denied IS DISTINCT FROM cpd.calculated_selling_denied
            RETURNING pd.product_id
        )
        SELECT DISTINCT product_id FROM updated_product_domains';
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

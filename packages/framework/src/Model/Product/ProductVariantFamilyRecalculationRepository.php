<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

class ProductVariantFamilyRecalculationRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     * @param array<string, \Doctrine\DBAL\ArrayParameterType|string> $parameterTypes
     * @param int[]|null $productIds
     * @return int[]
     */
    public function executeRecalculationAndGetChangedProductIds(
        string $recalculationSql,
        array $parameters,
        array $parameterTypes,
        ?array $productIds,
    ): array {
        if ($productIds === null) {
            $this->em->getConnection()->executeStatement($recalculationSql, $parameters, $parameterTypes);

            return [];
        }

        $parameters['productIds'] = $productIds;
        $parameterTypes['productIds'] = ArrayParameterType::INTEGER;

        $changedProductIds = $this->em->getConnection()->fetchFirstColumn($recalculationSql, $parameters, $parameterTypes);

        return array_map('intval', $changedProductIds);
    }

    public function getRequestedVariantFamilyRestrictionCteSql(bool $isRestrictedToProductIds): string
    {
        if (!$isRestrictedToProductIds) {
            return '';
        }

        return $this->getRequestedVariantFamilyProductIdsCteSql() . ',';
    }

    public function getRequestedVariantFamilyRestrictionWhereSql(
        string $productIdColumn,
        bool $isRestrictedToProductIds,
    ): string {
        if (!$isRestrictedToProductIds) {
            return '';
        }

        return 'WHERE ' . $this->getRequestedVariantFamilyProductIdsConditionSql($productIdColumn);
    }

    protected function getRequestedVariantFamilyProductIdsCteSql(): string
    {
        return 'requested_variant_family_product_ids AS (
            SELECT COALESCE(rp.main_variant_id, rp.id) AS product_id
            FROM products AS rp
            WHERE rp.id IN (:productIds)
            UNION
            SELECT vp.id
            FROM products AS rp
            JOIN products AS vp ON vp.main_variant_id = COALESCE(rp.main_variant_id, rp.id)
            WHERE rp.id IN (:productIds)
        )';
    }

    protected function getRequestedVariantFamilyProductIdsConditionSql(string $productIdColumn): string
    {
        return $productIdColumn . ' IN (SELECT product_id FROM requested_variant_family_product_ids)';
    }
}

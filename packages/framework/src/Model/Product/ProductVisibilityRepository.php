<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductVisibilityNotFoundException;

class ProductVisibilityRepository
{
    public function __construct(
        protected readonly EntityManagerDecorator $em,
        protected readonly Domain $domain,
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param int[]|null $productIds
     */
    public function refreshProductsVisibility(?array $productIds = null): void
    {
        if ($productIds === []) {
            return;
        }

        $changedRowsCount = $this->recalculateVisibility($productIds);

        if ($changedRowsCount === 0) {
            return;
        }

        // refresh entities after native query calls
        $this->em->refreshLoadedEntitiesByClassName(Product::class);
        $this->em->refreshLoadedEntitiesByClassName(ProductVisibility::class);
    }

    public function createAndRefreshProductVisibilitiesForPricingGroup(PricingGroup $pricingGroup, int $domainId): void
    {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO product_visibilities (product_id, pricing_group_id, domain_id, visible)
            SELECT id, :pricingGroupId, :domainId, false FROM products',
            [
                'pricingGroupId' => $pricingGroup->getId(),
                'domainId' => $domainId,
            ],
            [
                'pricingGroupId' => Types::INTEGER,
                'domainId' => Types::INTEGER,
            ],
        );
        $this->refreshProductsVisibility();
    }

    protected function getProductVisibilityRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductVisibility::class);
    }

    public function getProductVisibility(
        Product $product,
        PricingGroup $pricingGroup,
        int $domainId,
    ): ProductVisibility {
        $productVisibility = $this->getProductVisibilityRepository()->find([
            'product' => $product->getId(),
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => $domainId,
        ]);

        if ($productVisibility === null) {
            throw new ProductVisibilityNotFoundException();
        }

        return $productVisibility;
    }

    /**
     * @param int[] $productIds
     */
    public function preloadByProductIdsPricingGroupAndDomainId(
        array $productIds,
        PricingGroup $pricingGroup,
        int $domainId,
    ): void {
        if ($productIds === []) {
            return;
        }

        $this->em->createQueryBuilder()
            ->select('pv')
            ->from(ProductVisibility::class, 'pv')
            ->where('pv.product IN (:productIds)')
            ->andWhere('pv.pricingGroup = :pricingGroup')
            ->andWhere('pv.domainId = :domainId')
            ->setParameter('productIds', $productIds)
            ->setParameter('pricingGroup', $pricingGroup)
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[]
     */
    public function findProductVisibilitiesByDomainIdAndProduct(int $domainId, Product $product): array
    {
        return $this->getProductVisibilityRepository()->findBy([
            'product' => $product->getId(),
            'domainId' => $domainId,
        ]);
    }

    /**
     * @param array<int, int> $defaultPricingGroupIdsIndexedByDomainId
     */
    public function getCountOfDomainsProductIsVisibleOn(
        Product $product,
        array $defaultPricingGroupIdsIndexedByDomainId,
    ): int {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('count(pv.domainId)')
            ->from(ProductVisibility::class, 'pv')
            ->where('pv.product = :product')
            ->andWhere('pv.pricingGroup IN (:pricingGroupIds)')
            ->andWhere('pv.visible = TRUE')
            ->setParameter('product', $product)
            ->setParameter('pricingGroupIds', array_values($defaultPricingGroupIdsIndexedByDomainId));

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param int[]|null $productIds
     */
    protected function recalculateVisibility(?array $productIds): int
    {
        $parameters = [
            ...$this->getIndependentVisibilityParameters(),
            'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            'variantTypeVariant' => Product::VARIANT_TYPE_VARIANT,
        ];
        $parameterTypes = [
            ...$this->getIndependentVisibilityParameterTypes(),
            'variantTypeMain' => Types::STRING,
            'variantTypeVariant' => Types::STRING,
        ];

        $domainConfigs = $this->domain->getAllIncludingDomainConfigsWithoutDataCreated();
        $domainLocaleRows = [];

        foreach ($domainConfigs as $domainConfig) {
            $domainId = $domainConfig->getId();
            $domainLocaleRows[] = sprintf('(CAST(:domainId%1$d AS INTEGER), CAST(:locale%1$d AS TEXT))', $domainId);
            $parameters['domainId' . $domainId] = $domainId;
            $parameterTypes['domainId' . $domainId] = Types::INTEGER;
            $parameters['locale' . $domainId] = $domainConfig->getLocale();
            $parameterTypes['locale' . $domainId] = Types::STRING;
        }

        if ($productIds !== null) {
            $parameters['productIds'] = $productIds;
            $parameterTypes['productIds'] = ArrayParameterType::INTEGER;
        }

        return $this->em->getConnection()->executeStatement(
            $this->getVisibilityRecalculationSql($domainLocaleRows, $productIds !== null),
            $parameters,
            $parameterTypes,
        );
    }

    /**
     * @param string[] $domainLocaleRows
     */
    protected function getVisibilityRecalculationSql(array $domainLocaleRows, bool $isRestrictedToProductIds): string
    {
        $productIdsCondition = $isRestrictedToProductIds ? 'WHERE pv.product_id IN (:productIds)' : '';

        return 'WITH domain_locales (domain_id, locale) AS (
            VALUES ' . implode(', ', $domainLocaleRows) . '
        ),
        scoped_visibilities AS MATERIALIZED (
            SELECT
                pv.product_id,
                pv.domain_id,
                pv.pricing_group_id,
                p.variant_type,
                p.main_variant_id,
                COALESCE((' . $this->getIndependentVisibilityConditionSql() . '), FALSE) AS independently_visible
            FROM product_visibilities AS pv
            JOIN pricing_groups AS pg ON pg.id = pv.pricing_group_id AND pg.domain_id = pv.domain_id
            JOIN products AS p ON p.id = pv.product_id
            JOIN product_domains AS pd ON pd.product_id = pv.product_id AND pd.domain_id = pv.domain_id
            JOIN domain_locales AS dl ON dl.domain_id = pv.domain_id
            ' . $productIdsCondition . '
        ),
        variant_family_visibilities AS (
            SELECT
                sv.*,
                BOOL_OR(sv.independently_visible) FILTER (WHERE sv.variant_type = :variantTypeMain) OVER variant_family AS main_variant_independently_visible,
                BOOL_OR(sv.independently_visible) FILTER (WHERE sv.variant_type = :variantTypeVariant) OVER variant_family AS any_variant_independently_visible
            FROM scoped_visibilities AS sv
            WINDOW variant_family AS (PARTITION BY COALESCE(sv.main_variant_id, sv.product_id), sv.domain_id, sv.pricing_group_id)
        ),
        calculated_visibilities AS (
            SELECT
                vfv.product_id,
                vfv.domain_id,
                vfv.pricing_group_id,
                CASE
                    WHEN vfv.variant_type = :variantTypeVariant THEN ' . $this->getVariantVisibilityConditionSql() . '
                    WHEN vfv.variant_type = :variantTypeMain THEN ' . $this->getMainVariantVisibilityConditionSql() . '
                    ELSE vfv.independently_visible
                END AS visible
            FROM variant_family_visibilities AS vfv
        )
        UPDATE product_visibilities AS pv
        SET visible = cv.visible
        FROM calculated_visibilities AS cv
        WHERE pv.product_id = cv.product_id
            AND pv.domain_id = cv.domain_id
            AND pv.pricing_group_id = cv.pricing_group_id
            AND pv.visible IS DISTINCT FROM cv.visible';
    }

    protected function getVariantVisibilityConditionSql(): string
    {
        return '(vfv.independently_visible AND COALESCE(vfv.main_variant_independently_visible, FALSE))';
    }

    protected function getMainVariantVisibilityConditionSql(): string
    {
        return '(vfv.independently_visible AND COALESCE(vfv.any_variant_independently_visible, FALSE))';
    }

    protected function getIndependentVisibilityConditionSql(): string
    {
        return '(p.hidden = FALSE
            AND (p.selling_from IS NULL OR p.selling_from <= :now)
            AND (p.selling_to IS NULL OR p.selling_to >= :now)
            AND (
                p.variant_type = :variantTypeMain
                OR p.product_type = :inquiryProductType
                OR EXISTS (
                    SELECT 1
                    FROM product_manual_input_prices AS pmip
                    WHERE pmip.product_id = pv.product_id
                        AND pmip.pricing_group_id = pv.pricing_group_id
                        AND pmip.input_price > 0
                )
            )
            AND EXISTS (
                SELECT 1
                FROM product_translations AS pt
                WHERE pt.translatable_id = pv.product_id
                    AND pt.locale = dl.locale
                    AND pt.name IS NOT NULL
            )
            AND EXISTS (
                SELECT 1
                FROM product_category_domains AS pcd
                JOIN category_domains AS cd ON cd.category_id = pcd.category_id
                    AND cd.domain_id = pcd.domain_id
                WHERE pcd.product_id = pv.product_id
                    AND pcd.domain_id = pv.domain_id
                    AND cd.visible = TRUE
            )
            AND pd.domain_hidden = FALSE)';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getIndependentVisibilityParameters(): array
    {
        return [
            'now' => $this->clock->now(),
            'inquiryProductType' => ProductTypeEnum::TYPE_INQUIRY,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function getIndependentVisibilityParameterTypes(): array
    {
        return [
            'now' => Types::DATETIME_IMMUTABLE,
            'inquiryProductType' => Types::STRING,
        ];
    }

    /**
     * @param int[] $productIds
     * @return bool[]
     */
    public function getProductsVisibilitiesByPricingGroupAndDomainIndexedByProductId(
        array $productIds,
        int $pricingGroupId,
        int $domainId,
    ): array {
        $productVisibilities = $this
            ->getProductVisibilityRepository()
            ->createQueryBuilder('pv')
            ->select('IDENTITY(pv.product) productId, pv.visible isVisible')
            ->andWhere('pv.domainId = :domainId')
            ->andWhere('IDENTITY(pv.product) IN (:productIds)')
            ->andWhere('pv.pricingGroup = :pricingGroupId')
            ->setParameter('domainId', $domainId)
            ->setParameter('productIds', $productIds)
            ->setParameter('pricingGroupId', $pricingGroupId)
            ->getQuery()
            ->getResult();

        return array_column($productVisibilities, 'isVisible', 'productId');
    }
}

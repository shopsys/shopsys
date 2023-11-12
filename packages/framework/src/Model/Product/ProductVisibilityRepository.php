<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use DateTime;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductVisibilityNotFoundException;

class ProductVisibilityRepository
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     */
    public function __construct(
        protected readonly EntityManagerDecorator $em,
        protected readonly Domain $domain,
        protected readonly PricingGroupRepository $pricingGroupRepository,
    ) {
    }

    /**
     * @param int[]|null $productIds
     */
    public function refreshProductsVisibility(?array $productIds = null): void
    {
        $this->calculateIndependentVisibility($productIds);
        $this->hideVariantsWithInvisibleMainVariant($productIds);
        $this->hideMainVariantsWithoutVisibleVariants($productIds);

        // refresh entities after native query calls
        $this->em->refreshLoadedEntitiesByClassName(Product::class);
        $this->em->refreshLoadedEntitiesByClassName(ProductVisibility::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     */
    public function createAndRefreshProductVisibilitiesForPricingGroup(PricingGroup $pricingGroup, $domainId)
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

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductVisibilityRepository()
    {
        return $this->em->getRepository(ProductVisibility::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility
     */
    public function getProductVisibility(
        Product $product,
        PricingGroup $pricingGroup,
        $domainId,
    ) {
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
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[]
     */
    public function findProductVisibilitiesByDomainIdAndProduct($domainId, Product $product): array
    {
        return $this->getProductVisibilityRepository()->findBy([
            'product' => $product->getId(),
            'domainId' => $domainId,
        ]);
    }

    /**
     * @param int[]|null $productIds
     */
    protected function calculateIndependentVisibility(?array $productIds)
    {
        $variables = [
            'now' => new DateTime(),
            'locale' => null,
            'domainId' => null,
            'pricingGroupId' => null,
            'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
        ];

        $variableTypes = [
            'now' => Types::DATETIME_MUTABLE,
            'locale' => Types::STRING,
            'domainId' => Types::INTEGER,
            'pricingGroupId' => Types::INTEGER,
            'variantTypeMain' => Types::STRING,
        ];

        if ($productIds !== null) {
            $productIdsCondition = 'p.id IN (:productIds) AND';
            $variables['productIds'] = $productIds;
            $variableTypes['productIds'] = ArrayParameterType::INTEGER;
        } else {
            $productIdsCondition = '';
        }

        $query = 'UPDATE product_visibilities AS pv
            SET visible = CASE
                    WHEN (
                        p.calculated_hidden = FALSE
                        AND
                        (p.selling_from IS NULL OR p.selling_from <= :now)
                        AND
                        (p.selling_to IS NULL OR p.selling_to >= :now)
                        AND
                        (
                            p.variant_type = :variantTypeMain
                            OR
                            EXISTS (
                                SELECT 1
                                FROM product_calculated_prices as pcp
                                WHERE pcp.price_with_vat > 0
                                    AND pcp.product_id = pv.product_id
                                    AND pcp.pricing_group_id = pv.pricing_group_id
                            )
                        )
                        AND EXISTS (
                            SELECT 1
                            FROM product_translations AS pt
                            WHERE pt.translatable_id = pv.product_id
                                AND pt.locale = :locale
                                AND pt.name IS NOT NULL
                        )
                        AND EXISTS (
                            SELECT 1
                            FROM product_category_domains AS pcd
                            JOIN category_domains AS cd ON cd.category_id = pcd.category_id
                                AND cd.domain_id = pcd.domain_id
                            WHERE pcd.product_id = p.id
                                AND pcd.domain_id = pv.domain_id
                                AND cd.visible = TRUE
                        )
                    )
                    THEN TRUE
                    ELSE FALSE
                END
            FROM products AS p
            JOIN product_domains AS pd ON pd.product_id = p.id
            WHERE ' . $productIdsCondition . '
                p.id = pv.product_id
                AND pv.domain_id = :domainId
                AND pv.domain_id = pd.domain_id
                AND pv.pricing_group_id = :pricingGroupId
            ';

        foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
            $domain = $this->domain->getDomainConfigById($pricingGroup->getDomainId());
            $variables['locale'] = $domain->getLocale();
            $variables['domainId'] = $domain->getId();
            $variables['pricingGroupId'] = $pricingGroup->getId();

            $this->em->getConnection()->executeStatement(
                $query,
                $variables,
                $variableTypes,
            );
        }
    }

    /**
     * @param int[]|null $productIds
     */
    protected function hideVariantsWithInvisibleMainVariant(?array $productIds)
    {
        $variables = [
            'variantTypeVariant' => Product::VARIANT_TYPE_VARIANT,
        ];
        $variableTypes = [
            'variantTypeVariant' => Types::STRING,
        ];

        if ($productIds !== null) {
            $productIdsCondition = 'p.id IN (:productIds) AND';
            $variables['productIds'] = $productIds;
            $variableTypes['productIds'] = ArrayParameterType::INTEGER;
        } else {
            $productIdsCondition = '';
        }

        $this->em->getConnection()->executeStatement(
            'UPDATE product_visibilities AS pv
            SET visible = FALSE
            FROM products AS p
            WHERE ' . $productIdsCondition . ' 
                p.id = pv.product_id
                AND p.variant_type = :variantTypeVariant
                AND pv.visible = TRUE
                AND EXISTS (
                    SELECT 1
                    FROM product_visibilities mpv
                    WHERE mpv.product_id = p.main_variant_id
                        AND mpv.domain_id = pv.domain_id
                        AND mpv.pricing_group_id = pv.pricing_group_id
                        AND mpv.visible = FALSE
                )
            ',
            $variables,
            $variableTypes,
        );
    }

    /**
     * @param int[]|null $productIds
     */
    protected function hideMainVariantsWithoutVisibleVariants(?array $productIds)
    {
        $variables = [
            'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
        ];
        $variableTypes = [
            'variantTypeMain' => Types::STRING,
        ];

        if ($productIds !== null) {
            $productIdsCondition = 'p.id IN (:productIds) AND';
            $variables['productIds'] = $productIds;
            $variableTypes['productIds'] = ArrayParameterType::INTEGER;
        } else {
            $productIdsCondition = '';
        }

        $this->em->getConnection()->executeStatement(
            'UPDATE product_visibilities AS pv
            SET visible = FALSE
            FROM products AS p
            WHERE ' . $productIdsCondition . '
                p.id = pv.product_id
                AND p.variant_type = :variantTypeMain
                AND pv.visible = TRUE
                AND NOT EXISTS (
                    SELECT 1
                    FROM products vp
                    JOIN product_visibilities vpv ON
                        vpv.product_id = vp.id
                        AND vpv.domain_id = pv.domain_id
                        AND vpv.pricing_group_id = pv.pricing_group_id
                    WHERE vp.main_variant_id = p.id
                        AND vpv.visible = TRUE
                )
            ',
            $variables,
            $variableTypes,
        );
    }
}

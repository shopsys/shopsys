<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;

class ProductVisibilityRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository
     */
    protected $pricingGroupRepository;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        Domain $domain,
        PricingGroupRepository $pricingGroupRepository
    ) {
        $this->em = $em;
        $this->domain = $domain;
        $this->pricingGroupRepository = $pricingGroupRepository;
    }

    /**
     * @param int|null $productId
     */
    public function refreshProductsVisibility(?int $productId = null)
    {
        $this->calculateIndependentVisibility($productId);
        $this->hideVariantsWithInvisibleMainVariant($productId);
        $this->hideMainVariantsWithoutVisibleVariants($productId);
        $this->refreshGlobalProductVisibility($productId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return array
     */
    public function getProductIdsForRecalculationAffectedByCategory(Category $category): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('IDENTITY(pcd.product)')
            ->from(ProductCategoryDomain::class, 'pcd')
            ->join(Category::class, 'c', Join::WITH, 'c = pcd.category AND c.lft >= :lft AND c.rgt <= :rgt')
            ->setParameters([
                'lft' => $category->getLft(),
                'rgt' => $category->getRgt(),
            ])
            ->getQuery()
            ->getScalarResult();

        return array_unique(array_column($result, 1));
    }

    /**
     * @param int|null $productId
     */
    protected function refreshGlobalProductVisibility(?int $productId = null)
    {
        if ($productId !== null) {
            $productWhereClause = ' WHERE p.id = ' . $productId;
        } else {
            $productWhereClause = '';
        }

        $query = $this->em->createNativeQuery(
            'UPDATE products AS p
            SET calculated_visibility = (p.calculated_hidden = FALSE) AND EXISTS(
                    SELECT 1
                    FROM product_visibilities AS pv
                    WHERE pv.product_id = p.id
                        AND pv.visible = TRUE
                )
            ' . $productWhereClause,
            new ResultSetMapping()
        );
        $query->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int $domainId
     */
    public function createAndRefreshProductVisibilitiesForPricingGroup(PricingGroup $pricingGroup, $domainId)
    {
        $query = $this->em->createNativeQuery(
            'INSERT INTO product_visibilities (product_id, pricing_group_id, domain_id, visible)
            SELECT id, :pricingGroupId, :domainId, :calculatedVisibility FROM products',
            new ResultSetMapping()
        );
        $query->execute([
            'pricingGroupId' => $pricingGroup->getId(),
            'domainId' => $domainId,
            'calculatedVisibility' => false,
        ]);
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
        $domainId
    ) {
        $productVisibility = $this->getProductVisibilityRepository()->find([
            'product' => $product->getId(),
            'pricingGroup' => $pricingGroup->getId(),
            'domainId' => $domainId,
        ]);
        if ($productVisibility === null) {
            throw new \Shopsys\FrameworkBundle\Model\Product\Exception\ProductVisibilityNotFoundException();
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
     * @param int|null $productId
     */
    protected function calculateIndependentVisibility(?int $productId = null)
    {
        $now = new DateTime();
        if ($productId !== null) {
            $productWhereClause = ' AND p.id = ' . $productId;
        } else {
            $productWhereClause = '';
        }

        $query = $this->em->createNativeQuery(
            'UPDATE product_visibilities AS pv
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
            WHERE p.id = pv.product_id
                AND pv.domain_id = :domainId
                AND pv.domain_id = pd.domain_id
                AND pv.pricing_group_id = :pricingGroupId
            ' . $productWhereClause,
            new ResultSetMapping()
        );

        foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
            $domain = $this->domain->getDomainConfigById($pricingGroup->getDomainId());
            $query->execute([
                'now' => $now,
                'locale' => $domain->getLocale(),
                'domainId' => $domain->getId(),
                'pricingGroupId' => $pricingGroup->getId(),
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ]);
        }
    }

    /**
     * @param int|null $productId
     */
    protected function hideVariantsWithInvisibleMainVariant(?int $productId = null)
    {
        if ($productId !== null) {
            $productWhereClause = ' AND p.id = ' . $productId;
        } else {
            $productWhereClause = '';
        }

        $query = $this->em->createNativeQuery(
            'UPDATE product_visibilities AS pv
            SET visible = FALSE
            FROM products AS p
            WHERE p.id = pv.product_id
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
            ' . $productWhereClause,
            new ResultSetMapping()
        );

        $query->execute([
            'variantTypeVariant' => Product::VARIANT_TYPE_VARIANT,
        ]);
    }

    /**
     * @param int|null $productId
     */
    protected function hideMainVariantsWithoutVisibleVariants(?int $productId = null)
    {
        if ($productId !== null) {
            $productWhereClause = ' AND p.id = ' . $productId;
        } else {
            $productWhereClause = '';
        }

        $query = $this->em->createNativeQuery(
            'UPDATE product_visibilities AS pv
            SET visible = FALSE
            FROM products AS p
            WHERE p.id = pv.product_id
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
            ' . $productWhereClause,
            new ResultSetMapping()
        );

        $query->execute([
            'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Product;

use DateTime;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Types\Types;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository as BaseProductVisibilityRepository;

/**
 * @method markProductsForRecalculationAffectedByCategory(\App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\ProductVisibility getProductVisibility(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[] findProductVisibilitiesByDomainIdAndProduct(int $domainId, \App\Model\Product\Product $product)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository $pricingGroupRepository)
 */
class ProductVisibilityRepository extends BaseProductVisibilityRepository
{
    /**
     * @param array|null $productIds
     */
    protected function calculateIndependentVisibility(?array $productIds): void
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
                        AND 
                        (pd.domain_hidden = FALSE)
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
}

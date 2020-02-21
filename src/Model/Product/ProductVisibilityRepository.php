<?php

declare(strict_types=1);

namespace App\Model\Product;

use DateTime;
use Doctrine\ORM\Query\ResultSetMapping;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository as BaseProductVisibilityRepository;

/**
 * @method markProductsForRecalculationAffectedByCategory(\App\Model\Category\Category $category)
 * @method \Shopsys\FrameworkBundle\Model\Product\ProductVisibility getProductVisibility(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\ProductVisibility[] findProductVisibilitiesByDomainIdAndProduct(int $domainId, \App\Model\Product\Product $product)
 */
class ProductVisibilityRepository extends BaseProductVisibilityRepository
{
    /**
     * @param bool $onlyMarkedProducts
     */
    protected function calculateIndependentVisibility($onlyMarkedProducts)
    {
        $now = new DateTime();
        if ($onlyMarkedProducts) {
            $onlyMarkedProductsCondition = ' AND p.recalculate_visibility = TRUE';
        } else {
            $onlyMarkedProductsCondition = '';
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
                            pd.low_price_with_vat > 0
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
            ' . $onlyMarkedProductsCondition,
            new ResultSetMapping()
        );

        foreach ($this->domain->getAll() as $domain) {
            $query->execute([
                'now' => $now,
                'locale' => $domain->getLocale(),
                'domainId' => $domain->getId(),
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Model\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator as BaseProductSellingDeniedRecalculator;

/**
 * @method calculateSellingDeniedForProduct(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getProductsForCalculations(\App\Model\Product\Product $product)
 */
class ProductSellingDeniedRecalculator extends BaseProductSellingDeniedRecalculator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(EntityManagerInterface $entityManager, Domain $domain)
    {
        parent::__construct($entityManager);

        $this->domain = $domain;
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    protected function calculate(array $products = [])
    {
        $this->calculateIndependent($products);
        $this->propagateCalculatedSaleExclusionToCalculatedSellingDenied($products);
        $this->propagateMainVariantSellingDeniedToVariants($products);
        $this->propagateVariantsSellingDeniedToMainVariant($products);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    protected function calculateIndependent(array $products)
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedSellingDenied', 'p.sellingDenied');
        if (count($products) > 0) {
            $qb->andWhere('p IN (:products)')->setParameter('products', $products);
        }
        $qb->getQuery()->execute();

        $this->calculatePerDomain($products);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    private function calculatePerDomain(array $products)
    {
        $query = 'UPDATE product_domains AS pd
            SET calculated_sale_exclusion = CASE
                    WHEN (
                        p.calculated_selling_denied = TRUE
                        OR
                        pd.domain_hidden = TRUE
                        OR 
                        (
                            (
                                pd.sale_exclusion = TRUE
                                OR                        
                                p.preorder = FALSE
                            )  
                            AND
                            ( 
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
            ' . (count($products) > 0 ? ' AND p.id IN (:productIds)' : '');

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $params = [];
        $params['productIds'] = $productIds;
        $params['variantTypeMain'] = Product::VARIANT_TYPE_MAIN;

        foreach ($this->domain->getAll() as $domain) {
            $params['domainId'] = $domain->getId();

            $this->em->getConnection()->executeStatement(
                $query,
                $params,
                [
                    'productIds' => Connection::PARAM_INT_ARRAY,
                    'variantTypeMain' => Types::STRING,
                    'domainId' => Types::INTEGER,
                ]
            );
        }
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    private function propagateCalculatedSaleExclusionToCalculatedSellingDenied(array $products): void
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
            ' . (count($products) > 0 ? ' AND p.id IN (:productIds)' : '');

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $this->em->getConnection()->executeStatement(
            $query,
            ['productIds' => $productIds],
            ['productIds' => Connection::PARAM_INT_ARRAY],
        );
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    protected function propagateMainVariantSellingDeniedToVariants(array $products)
    {
        parent::propagateMainVariantSellingDeniedToVariants($products);

        $this->propagateMainVariantSellingDeniedToVariantsCalculatedSaleExclusion($products);
        $this->propagateMainVariantCalculateSaleExclusionToVariantsCalculatedSaleExclusion($products);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    private function propagateMainVariantSellingDeniedToVariantsCalculatedSaleExclusion(array $products): void
    {
        $query = 'UPDATE product_domains as pd
                SET calculated_sale_exclusion = TRUE
                FROM products as p
                JOIN products as m ON p.main_variant_id = m.id
                WHERE m.variant_type = :variantTypeMain
                AND pd.product_id = p.id
                AND pd.calculated_sale_exclusion = FALSE
                AND p.calculated_selling_denied = TRUE
            ' . (count($products) > 0 ? ' AND m.id IN (:productIds)' : '');

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
                'variantTypeMain' => Types::STRING,
            ],
        );
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    private function propagateMainVariantCalculateSaleExclusionToVariantsCalculatedSaleExclusion(array $products): void
    {
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
            ' . (count($products) > 0 ? ' AND m.id IN (:productIds)' : '');

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
                'variantTypeMain' => Types::STRING,
            ],
        );
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    protected function propagateVariantsSellingDeniedToMainVariant(array $products)
    {
        parent::propagateVariantsSellingDeniedToMainVariant($products);

        $this->propagateVariantsSaleExclusionToMainVariantCalculateSaleExclusion($products);
        $this->propagateVariantsSaleExclusionToMainVariantCalculateSellingDenied($products);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    private function propagateVariantsSaleExclusionToMainVariantCalculateSaleExclusion(array $products): void
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
                    JOIN product_domains as pdv ON pdv.product_id = v.id AND pdv.domain_id = pd.domain_id
                    WHERE v.main_variant_id = p.id
                        AND pdv.calculated_sale_exclusion = FALSE
                )
            ' . (count($products) > 0 ? ' AND p.id IN (:productIds)' : '');

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
                'variantTypeMain' => Types::STRING,
            ]
        );
    }

    /**
     * @param \App\Model\Product\Product[] $products
     */
    private function propagateVariantsSaleExclusionToMainVariantCalculateSellingDenied(array $products): void
    {
        $query = 'UPDATE products as p
                SET calculated_selling_denied = TRUE
                WHERE p.variant_type = :variantTypeMain
                AND p.calculated_selling_denied = FALSE
                AND NOT EXISTS (
                    SELECT 1
                    FROM products as v
                    JOIN product_domains as pdv ON pdv.product_id = v.id
                    WHERE v.main_variant_id = p.id
                        AND pdv.calculated_sale_exclusion = FALSE
                )
            ' . (count($products) > 0 ? ' AND p.id IN (:productIds)' : '');

        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        $this->em->getConnection()->executeStatement(
            $query,
            [
                'productIds' => $productIds,
                'variantTypeMain' => Product::VARIANT_TYPE_MAIN,
            ],
            [
                'productIds' => Connection::PARAM_INT_ARRAY,
                'variantTypeMain' => Types::STRING,
            ]
        );
    }
}

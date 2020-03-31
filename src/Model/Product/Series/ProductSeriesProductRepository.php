<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Product;
use App\Model\Product\ProductTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ProductSeriesProductRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(EntityManagerInterface $em, Localization $localization)
    {
        $this->em = $em;
        $this->localization = $localization;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('psp')
            ->from(ProductSeriesProduct::class, 'psp');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Series\ProductSeriesProduct[]
     */
    public function findByProduct(Product $product): array
    {
        return $this->getQueryBuilder()
            ->where('psp.product = :product')
            ->setParameter('product', $product)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getProductSeriesProductsQueryBuilderByProductSeries(ProductSeries $productSeries): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->select('pt.name as name, IDENTITY(psp.product) as id')
            ->join(ProductTranslation::class, 'pt', Join::WITH, 'pt.translatable = psp.product')
            ->where('psp.productSeries = :productSeries')
            ->andWhere('pt.locale = :locale')
            ->setParameter('productSeries', $productSeries)
            ->setParameter('locale', $this->localization->getAdminLocale());
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \App\Model\Product\Series\ProductSeriesProduct[]
     */
    public function findByProductSeries(ProductSeries $productSeries): array
    {
        return $this->getQueryBuilder()
            ->where('psp.productSeries = :productSeries')
            ->setParameter('productSeries', $productSeries)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Series\ProductSeriesProduct|null
     */
    public function findByProductSeriesAndProduct(ProductSeries $productSeries, Product $product): ?ProductSeriesProduct
    {
        return $this->getQueryBuilder()
            ->where('psp.product = :product')
            ->andWhere('psp.productSeries = :productSeries')
            ->setParameter('product', $product)
            ->setParameter('productSeries', $productSeries)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

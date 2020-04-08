<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Product;
use App\Model\Product\ProductRepository;
use App\Model\Product\ProductTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
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
     * @var \App\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        EntityManagerInterface $em,
        Localization $localization,
        ProductRepository $productRepository,
        Domain $domain
    ) {
        $this->em = $em;
        $this->localization = $localization;
        $this->productRepository = $productRepository;
        $this->domain = $domain;
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
     * @return array
     */
    public function findAvailableProductsByProductSeries(ProductSeries $productSeries): array
    {
        $queryBuilder = $this->productRepository->getAllVisibleQueryBuilder($this->domain->getId());

        $this->productRepository->filterTemporaryExcludedProducts($queryBuilder, $this->domain->getId());
        $this->productRepository->filterSellingDenied($queryBuilder);
        $this->productRepository->addDomain($queryBuilder, $this->domain->getId());

        return $queryBuilder->join(ProductSeriesProduct::class, 'psp', Join::WITH, 'psp.product = p')
            ->andWhere('psp.productSeries = :productSeries')
            ->setParameter('productSeries', $productSeries)
            ->getQuery()
            ->execute();
    }
}

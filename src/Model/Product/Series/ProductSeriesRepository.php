<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Product;
use App\Model\Product\Series\Category\ProductSeriesCategory;
use App\Model\Product\Series\Exception\ProductSeriesNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ProductSeriesRepository
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
    public function __construct(
        EntityManagerInterface $em,
        Localization $localization
    ) {
        $this->em = $em;
        $this->localization = $localization;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    private function getRepository()
    {
        return $this->em->getRepository(ProductSeries::class);
    }

    /**
     * @param int $id
     * @return \App\Model\Product\Series\ProductSeries
     */
    public function getById(int $id): ProductSeries
    {
        /** @var \App\Model\Product\Series\ProductSeries $productSeries */
        $productSeries = $this->getRepository()->find($id);
        if ($productSeries == null) {
            $message = 'Product series with ID ' . $id . ' not found';
            throw new ProductSeriesNotFoundException($message);
        }
        return $productSeries;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('ps')
            ->from(ProductSeries::class, 'ps');
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getVisibleQueryBuilderByDomainId(int $domainId): QueryBuilder
    {
        return $this->getQueryBuilderByDomainId($domainId)
            ->andWhere('psd.hidden = FALSE');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllProductSeriesQueryBuilderByMainDomain(): QueryBuilder
    {
        return $this->getQueryBuilderByDomainId(Domain::MAIN_ADMIN_DOMAIN_ID)
            ->join(ProductSeriesTranslation::class, 'pst', Join::WITH, 'pst.translatable = ps')
            ->addSelect('pst')
            ->andWhere('pst.locale = :locale')
            ->orderBy('ps.id', 'DESC')
            ->setParameter('locale', $this->localization->getAdminLocale());
    }

    /**
     * @param int $domainId
     * @return array
     */
    public function getAllVisibleProductSeriesByDomainId(int $domainId): array
    {
        return $this->getVisibleQueryBuilderByDomainId($domainId)
            ->orderBy('ps.id', 'DESC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $id
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries|null
     */
    public function findVisibleProductSeriesByIdAndDomainId(int $id, int $domainId): ?ProductSeries
    {
        return $this->getVisibleQueryBuilderByDomainId($domainId)
            ->andWhere('ps.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilderByDomainId(int $domainId): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->join(ProductSeriesDomain::class, 'psd', Join::WITH, 'psd.productSeries = ps')
            ->where('psd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @param \App\Model\Product\Series\Category\ProductSeriesCategory $productSeriesCategory
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getByProductSeriesCategoryAndDomainId(ProductSeriesCategory $productSeriesCategory, int $domainId): array
    {
        return $this->getQueryBuilderByDomainId($domainId)
            ->join('ps.productSeriesCategories', 'psc', Join::WITH, 'psc = :productSeriesCategory')
            ->setParameter('productSeriesCategory', $productSeriesCategory)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Series\ProductSeries|null
     */
    public function findByAkeneoCode(string $akeneoCode): ?ProductSeries
    {
        return $this->getRepository()->findOneBy(['akeneoCode' => $akeneoCode]);
    }

    /**
     * @return int[]
     */
    public function findProductSeriesIdsWithAkeneoCode(): array
    {
        $result = $this->getQueryBuilder()
            ->select('ps.id')
            ->where('ps.akeneoCode IS NOT NULL')
            ->getQuery()
            ->execute();

        return array_map('reset', $result);
    }

    /**
     * @return string[]
     */
    public function findProductSeriesCodesWithAkeneoCode(): array
    {
        $result = $this->getQueryBuilder()
            ->select('ps.akeneoCode')
            ->where('ps.akeneoCode IS NOT NULL')
            ->getQuery()
            ->execute();

        return array_map('reset', $result);
    }

    /**
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getNamesWithIds(): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder
            ->join(ProductSeriesTranslation::class, 'pst', Join::WITH, 'pst.translatable = ps')
            ->andWhere('pst.locale = :locale')
            ->orderBy('pst.name', 'ASC')
            ->setParameter('locale', $this->localization->getAdminLocale());

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Product\Series\ProductSeries[]
     */
    public function getAllVisibleByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->getVisibleQueryBuilderByDomainId($domainId)
            ->join(ProductSeriesProduct::class, 'psp', Join::WITH, 'psp.productSeries = ps')
            ->andWhere('psp.product = :product')
            ->orderBy('ps.id', 'DESC')
            ->setParameter('product', $product)
            ->getQuery()
            ->execute();
    }
}

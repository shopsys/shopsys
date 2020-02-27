<?php

declare(strict_types=1);

namespace App\Model\Product\Series\Category;

use App\Model\Product\Series\Category\Exception\ProductSeriesCategoryNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class ProductSeriesCategoryRepository
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
        return $this->em->getRepository(ProductSeriesCategory::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('psc')
            ->from(ProductSeriesCategory::class, 'psc');
    }

    /**
     * @param int $productSeriesCategoryId
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory|null
     */
    public function findById(int $productSeriesCategoryId): ?ProductSeriesCategory
    {
        return $this->getRepository()->find($productSeriesCategoryId);
    }

    /**
     * @param int $productSeriesCategoryId
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory
     */
    public function getById(int $productSeriesCategoryId): ProductSeriesCategory
    {
        $productSeriesCategory = $this->findById($productSeriesCategoryId);
        if ($productSeriesCategory === null) {
            throw new ProductSeriesCategoryNotFoundException($productSeriesCategoryId);
        }

        return $productSeriesCategory;
    }

    /**
     * @return \App\Model\Product\Series\Category\ProductSeriesCategory[]
     */
    public function getAllProductSeriesCategories(): array
    {
        return $this->getRepository()->findAll();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilderByDomainId(int $domainId): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->join(ProductSeriesCategoryDomain::class, 'pscd', Join::WITH, 'pscd.productSeriesCategory = psc')
            ->where('pscd.domainId = :domainId')
            ->setParameter('domainId', $domainId);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllProductSeriesQueryBuilderByMainDomain(): QueryBuilder
    {
        return $this->getQueryBuilderByDomainId(Domain::MAIN_ADMIN_DOMAIN_ID)
            ->join(ProductSeriesCategoryTranslation::class, 'psct', Join::WITH, 'psct.translatable = psc')
            ->addSelect('psct')
            ->andWhere('psct.locale = :locale')
            ->orderBy('psc.id', 'DESC')
            ->setParameter('locale', $this->localization->getAdminLocale());
    }
}

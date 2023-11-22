<?php

declare(strict_types=1);

namespace App\Model\Product\Filter;

use App\Component\Doctrine\OrderByCollationHelper;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\FlagFilterChoiceRepository as BaseFlagFilterChoiceRepository;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method __construct(\App\Model\Product\ProductRepository $productRepository)
 */
class FlagFilterChoiceRepository extends BaseFlagFilterChoiceRepository
{
    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param \App\Model\Category\Category $category
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesInCategory($domainId, PricingGroup $pricingGroup, $locale, Category $category): array
    {
        $productsQueryBuilder = $this->productRepository->getSellableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getVisibleFlagsByProductsQueryBuilderForDomain($productsQueryBuilder, $locale, $domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param \App\Model\Product\Brand\Brand $brand
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesForBrand(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        Brand $brand,
    ): array {
        $productsQueryBuilder = $this->productRepository->getListableForBrandQueryBuilder(
            $domainId,
            $pricingGroup,
            $brand,
        );

        return $this->getVisibleFlagsByProductsQueryBuilderForDomain($productsQueryBuilder, $locale, $domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @param string|null $searchText
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesForSearch($domainId, PricingGroup $pricingGroup, $locale, $searchText): array
    {
        $productsQueryBuilder = $this->productRepository
            ->getSellableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getVisibleFlagsByProductsQueryBuilderForDomain($productsQueryBuilder, $locale, $domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesForAll(int $domainId, PricingGroup $pricingGroup, string $locale): array
    {
        $productsQueryBuilder = $this->productRepository->getAllListableQueryBuilder(
            $domainId,
            $pricingGroup,
        );

        return $this->getVisibleFlagsByProductsQueryBuilderForDomain($productsQueryBuilder, $locale, $domainId);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param string $locale
     * @param int $domainId
     * @return \App\Model\Product\Flag\Flag[]
     */
    protected function getVisibleFlagsByProductsQueryBuilderForDomain(
        QueryBuilder $productsQueryBuilder,
        string $locale,
        int $domainId,
    ): array {
        $clonedProductsQueryBuilder = clone $productsQueryBuilder;

        $clonedProductsQueryBuilder
            ->select('1')
            ->join('p.domains', 'pd0')
            ->join('pd0.flags', 'df')
            ->andWhere('df.id = f.id')
            ->andWhere('f.visible = true')
            ->andWhere('pd0.domainId = :domainId')
            ->resetDQLPart('orderBy')
            ->setParameter('domainId', $domainId);

        $flagsQueryBuilder = $productsQueryBuilder->getEntityManager()->createQueryBuilder();
        $flagsQueryBuilder
            ->select('f, ft')
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->andWhere($flagsQueryBuilder->expr()->exists($clonedProductsQueryBuilder))
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('locale', $locale);

        foreach ($clonedProductsQueryBuilder->getParameters() as $parameter) {
            $flagsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
        }

        return $flagsQueryBuilder->getQuery()->execute();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $productsQueryBuilder
     * @param string $locale
     * @return \App\Model\Product\Flag\Flag[]
     */
    protected function getVisibleFlagsByProductsQueryBuilder(QueryBuilder $productsQueryBuilder, $locale): array
    {
        $clonedProductsQueryBuilder = clone $productsQueryBuilder;

        $clonedProductsQueryBuilder
            ->select('1')
            ->join('p.flags', 'pf')
            ->andWhere('pf.id = f.id')
            ->andWhere('f.visible = true')
            ->resetDQLPart('orderBy');

        $flagsQueryBuilder = $productsQueryBuilder->getEntityManager()->createQueryBuilder();
        $flagsQueryBuilder
            ->select('f, ft')
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->andWhere($flagsQueryBuilder->expr()->exists($clonedProductsQueryBuilder))
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('locale', $locale);

        foreach ($clonedProductsQueryBuilder->getParameters() as $parameter) {
            $flagsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
        }

        return $flagsQueryBuilder->getQuery()->execute();
    }
}

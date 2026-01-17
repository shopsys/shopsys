<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class FlagFilterChoiceRepository
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesInCategory(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        Category $category,
    ): array {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getVisibleFlagsByProductsQueryBuilder($productsQueryBuilder, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
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

        return $this->getVisibleFlagsByProductsQueryBuilder($productsQueryBuilder, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesForAll(int $domainId, PricingGroup $pricingGroup, string $locale): array
    {
        $productsQueryBuilder = $this->productRepository->getAllListableQueryBuilder(
            $domainId,
            $pricingGroup,
        );

        return $this->getVisibleFlagsByProductsQueryBuilder($productsQueryBuilder, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    public function getFlagFilterChoicesForSearch(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText,
    ): array {
        $productsQueryBuilder = $this->productRepository
            ->getListableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getVisibleFlagsByProductsQueryBuilder($productsQueryBuilder, $locale);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getVisibleFlagsByProductsQueryBuilder(QueryBuilder $productsQueryBuilder, string $locale): array
    {
        $clonedProductsQueryBuilder = clone $productsQueryBuilder;

        $clonedProductsQueryBuilder
            ->select('1')
            ->join('pd.flags', 'pf')
            ->andWhere('pf.id = f.id')
            ->andWhere('f.visible = true')
            ->resetDQLPart('orderBy');

        $flagsQueryBuilder = $productsQueryBuilder->getEntityManager()->createQueryBuilder();
        $flagsQueryBuilder
            ->select('f, ft')
            ->from(Flag::class, 'f')
            ->join('f.translations', 'ft', Join::WITH, 'ft.locale = :locale')
            ->andWhere($flagsQueryBuilder->expr()->exists($clonedProductsQueryBuilder))
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('ft.name', $locale), 'asc')
            ->setParameter('locale', $locale);

        foreach ($clonedProductsQueryBuilder->getParameters() as $parameter) {
            $flagsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
        }

        return $flagsQueryBuilder->getQuery()->execute();
    }
}

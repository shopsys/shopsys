<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class BrandFilterChoiceRepository
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly Domain $domain,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandFilterChoicesInCategory(
        int $domainId,
        PricingGroup $pricingGroup,
        Category $category,
    ): array {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category,
        );

        return $this->getBrandsByProductsQueryBuilder($productsQueryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandFilterChoicesForSearch(
        int $domainId,
        PricingGroup $pricingGroup,
        string $locale,
        ?string $searchText,
    ): array {
        $productsQueryBuilder = $this->productRepository
            ->getListableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getBrandsByProductsQueryBuilder($productsQueryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandFilterChoicesForAll(int $domainId, PricingGroup $pricingGroup): array
    {
        $productsQueryBuilder = $this->productRepository
            ->getAllListableQueryBuilder($domainId, $pricingGroup);

        return $this->getBrandsByProductsQueryBuilder($productsQueryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    protected function getBrandsByProductsQueryBuilder(QueryBuilder $productsQueryBuilder): array
    {
        $clonedProductsQueryBuilder = clone $productsQueryBuilder;

        $clonedProductsQueryBuilder
            ->select('1')
            ->join('p.brand', 'pb')
            ->andWhere('pb.id = b.id')
            ->resetDQLPart('orderBy');

        $brandsQueryBuilder = $productsQueryBuilder->getEntityManager()->createQueryBuilder();
        $brandsQueryBuilder
            ->select('b')
            ->from(Brand::class, 'b')
            ->andWhere($brandsQueryBuilder->expr()->exists($clonedProductsQueryBuilder))
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('b.name', $this->domain->getLocale()), 'asc');

        foreach ($clonedProductsQueryBuilder->getParameters() as $parameter) {
            $brandsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
        }

        return $brandsQueryBuilder->getQuery()->execute();
    }
}

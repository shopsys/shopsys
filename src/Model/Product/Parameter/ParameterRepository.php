<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;

/**
 * @method \Doctrine\ORM\QueryBuilder getProductParameterValuesByProductQueryBuilder(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProductSortedByName(\App\Model\Product\Product $product, string $locale)
 * @property \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $entityManager, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactoryInterface $parameterValueFactory, \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory)
 * @method \App\Model\Product\Parameter\Parameter|null findById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\ParameterValue findOrCreateParameterValueByValueTextAndLocale(string $valueText, string $locale)
 * @method \App\Model\Product\Parameter\ParameterValue getParameterValueByValueTextAndLocale(string $valueText, string $locale)
 * @method string[][] getParameterValuesIndexedByProductIdAndParameterNameForProducts(\App\Model\Product\Product[] $products, string $locale)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 */
class ParameterRepository extends BaseParameterRepository
{
    /**
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategory(Category $category, int $domainId): array
    {
        $queryBuilder = $this->getParameterRepository()->createQueryBuilder('p')
            ->select('p')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'p = ppv.parameter')
            ->groupBy('p');

        $this->applyCategorySeoConditions($queryBuilder, $category, $domainId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesUsedByProductsInCategoryByParameter(
        Category $category,
        Parameter $parameter,
        int $domainId,
        string $locale
    ): array {
        $queryBuilder = $this->getParameterValueRepository()->createQueryBuilder('pv')
            ->select('pv')
            ->andWhere('ppv.parameter = :parameter')
            ->setParameter('parameter', $parameter)
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value and pv.locale = :locale')
            ->setParameter(':locale', $locale)
            ->groupBy('pv');

        $this->applyCategorySeoConditions($queryBuilder, $category, $domainId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     */
    private function applyCategorySeoConditions(QueryBuilder $queryBuilder, Category $category, int $domainId): void
    {
        $queryBuilder
            ->join(Product::class, 'product', Join::WITH, 'ppv.product = product')
            ->join(ProductCategoryDomain::class, 'pcd', Join::WITH, 'product = pcd.product')
            ->andWhere('pcd.category = :category')
            ->andWhere('pcd.domainId = :domainId')
            ->setParameter('category', $category)
            ->setParameter('domainId', $domainId);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getParameterGroupRepository(): EntityRepository
    {
        return $this->em->getRepository(ParameterGroup::class);
    }

    /**
     * @param string[] $namesByLocale
     * @return \App\Model\Product\Parameter\ParameterGroup|null
     */
    public function findParameterGroupByNames(array $namesByLocale): ?ParameterGroup
    {
        $queryBuilder = $this->getParameterGroupRepository()->createQueryBuilder('pg');
        $index = 0;

        foreach ($namesByLocale as $locale => $name) {
            $alias = 'pgt' . $index;
            $localeParameterName = 'locale' . $index;
            $nameParameterName = 'name' . $index;
            $queryBuilder->join(
                'pg.translations',
                $alias,
                Join::WITH,
                'pg = ' . $alias . '.translatable
                    AND ' . $alias . '.locale = :' . $localeParameterName . '
                    AND ' . $alias . '.name = :' . $nameParameterName
            );
            $queryBuilder->setParameter($localeParameterName, $locale);
            $queryBuilder->setParameter($nameParameterName, $name);
            $index++;
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductSortedByNameQueryBuilder(BaseProduct $product, $locale): QueryBuilder
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ppv')
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('p.translations', 'pt')
            ->leftJoin('p.group', 'pg')
            ->where('ppv.product = :product_id')
            ->andWhere('pt.locale = :locale')
            ->setParameters([
                                'product_id' => $product->getId(),
                                'locale' => $locale,
                            ])
            ->addOrderBy('pg.orderingPriority', 'ASC')
            ->addOrderBy('p.orderingPriority', 'ASC');

        return $queryBuilder;
    }
}

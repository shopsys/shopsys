<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class AffectedProductsRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository<\Shopsys\FrameworkBundle\Model\Product\Product>
     */
    protected function getProductRepository(): EntityRepository
    {
        return $this->em->getRepository(Product::class);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithBrand(Brand $brand): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->where('p.brand = :brand')
            ->setParameter('brand', $brand);

        return $this->getSingleColumnResultFromQueryBuilder($queryBuilder);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithCategory(Category $category): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->join('p.productCategoryDomains', 'pcd')
            ->where('pcd.category = :category')
            ->setParameter('category', $category);

        return $this->getSingleColumnResultFromQueryBuilder($queryBuilder);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithFlag(Flag $flag): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->innerJoin('p.domains', 'pd')
            ->leftJoin('pd.flags', 'f')
            ->where('f.id = :flag')
            ->setParameter('flag', $flag);

        return $this->getSingleColumnResultFromQueryBuilder($queryBuilder);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithParameter(Parameter $parameter): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->innerJoin(ProductParameterValue::class, 'ppv', 'WITH', 'ppv.product = p')
            ->where('ppv.parameter = :parameter')
            ->setParameter('parameter', $parameter);

        return $this->getSingleColumnResultFromQueryBuilder($queryBuilder);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithParameterGroup(ParameterGroup $parameterGroup): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->join(ProductParameterValue::class, 'ppv', 'WITH', 'ppv.product = p')
            ->join('ppv.parameter', 'parameter')
            ->where('parameter.group = :parameterGroupId')
            ->setParameter('parameterGroupId', $parameterGroup->getId());

        return $this->getSingleColumnResultFromQueryBuilder($queryBuilder);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithUnit(Unit $unit): array
    {
        $queryBuilder = $this->createQueryBuilder()
            ->where('p.unit = :unit')
            ->setParameter('unit', $unit);

        return $this->getSingleColumnResultFromQueryBuilder($queryBuilder);
    }

    protected function getSingleColumnResultFromQueryBuilder(QueryBuilder $queryBuilder): array
    {
        return $queryBuilder
            ->getQuery()
            ->getSingleColumnResult();
    }

    protected function createQueryBuilder(): QueryBuilder
    {
        return $this->getProductRepository()
            ->createQueryBuilder('p')
            ->select('p.id');
    }
}

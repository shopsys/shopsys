<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Parameter\Exception\ParameterGroupNotFoundException;
use App\Model\Product\Product;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;

/**
 * @method \Doctrine\ORM\QueryBuilder getProductParameterValuesByProductQueryBuilder(Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProduct(Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProductSortedByName(Product $product, string $locale)
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
     * @return \App\Model\Product\Parameter\Parameter[]
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
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param int $domainId
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue[]
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
     * @param int $parameterValueId
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        $parameterValue = $this->getParameterValueRepository()->find($parameterValueId);

        if ($parameterValue === null) {
            $message = 'ParameterValue with ID ' . $parameterValueId . ' not found.';
            throw new \App\Model\Product\Parameter\Exception\ParameterValueNotFoundException($message);
        }

        return $parameterValue;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \App\Model\Category\Category $category
     * @param int $domainId
     */
    private function applyCategorySeoConditions(QueryBuilder $queryBuilder, Category $category, int $domainId): void
    {
        $queryBuilder
            ->join(BaseProduct::class, 'product', Join::WITH, 'ppv.product = product')
            ->join(ProductCategoryDomain::class, 'pcd', Join::WITH, 'product = pcd.product')
            ->andWhere('pcd.category = :category')
            ->andWhere('pcd.domainId = :domainId')
            ->setParameter('category', $category)
            ->setParameter('domainId', $domainId);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getParameterGroupRepository(): EntityRepository
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
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\ParameterGroup|null
     */
    public function findParameterGroupByAkeneoCode(string $akeneoCode): ?ParameterGroup
    {
        /** @var \App\Model\Product\Parameter\ParameterGroup|null $parameterGroup */
        $parameterGroup = $this->getParameterGroupRepository()->findOneBy(['akeneoCode' => $akeneoCode]);

        return $parameterGroup;
    }

    /**
     * @param int $parameterGroupId
     * @return \App\Model\Product\Parameter\ParameterGroup
     */
    public function getParameterGroupById(int $parameterGroupId): ParameterGroup
    {
        $parameterGroup = $this->getParameterGroupRepository()->find($parameterGroupId);

        if ($parameterGroup === null) {
            throw new ParameterGroupNotFoundException(sprintf('Parameter group with ID %s not found', $parameterGroupId));
        }

        return $parameterGroup;
    }

    /**
     * @param string $akeneoCode
     * @return \App\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByAkeneoCode(string $akeneoCode): ?Parameter
    {
        /** @var \App\Model\Product\Parameter\Parameter|null $parameter */
        $parameter = $this->getParameterRepository()->findOneBy(['akeneoCode' => $akeneoCode]);

        return $parameter;
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

    /**
     * @param \App\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function findOrCreateParameterValueByParameterValueData(ParameterValueData $parameterValueData): ParameterValue
    {
        $parameterValue = $this->getParameterValueRepository()->findOneBy([
            'text' => $parameterValueData->text,
            'locale' => $parameterValueData->locale,
        ]);

        if ($parameterValue === null) {
            $parameterValue = $this->parameterValueFactory->create($parameterValueData);
            $this->em->persist($parameterValue);
            // Doctrine's identity map is not cache.
            // We have to flush now, so that next findOneBy() finds new ParameterValue.
            $this->em->flush();
        }

        /** @var \App\Model\Product\Parameter\ParameterValue $parameterValue */
        return $parameterValue;
    }

    /**
     * @return int[]
     */
    public function getAllAkeneoParameterIds(): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('p.id')
            ->from(Parameter::class, 'p')
            ->where('p.akeneoCode IS NOT NULL')
            ->getQuery()
            ->execute();

        return array_map('reset', $result);
    }

    /**
     * @param string $locale
     * @param string $type
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderParameterValuesUsedByProductsByLocaleAndType(
        string $locale,
        string $type
    ): QueryBuilder {
        return $this->getParameterValueRepository()->createQueryBuilder('pv')
            ->select('pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value and pv.locale = :locale')
            ->join(Parameter::class, 'p', Join::WITH, 'ppv.parameter = p and p.parameterType = :type')
            ->setParameter(':locale', $locale)
            ->setParameter(':type', $type)
            ->groupBy('pv')
            ->orderBy('pv.text');
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesForVariantsByMainProductAndParameter(BaseProduct $product, Parameter $parameter, string $locale): array
    {
        return $this->getParameterValueRepository()
            ->createQueryBuilder('pv')
            ->select('pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value and pv.locale = :locale')
            ->join('ppv.product', 'p', Join::WITH, 'p.mainVariant = :product')
            ->where('ppv.parameter = :parameter')
            ->groupBy('pv')
            ->orderBy('pv.text')
            ->setParameters([
                'locale' => $locale,
                'product' => $product,
                'parameter' => $parameter,
                ])
            ->getQuery()
            ->execute();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Product\Parameter\Parameter $parameter
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueForVariantByProductVariantAndParameter(BaseProduct $product, Parameter $parameter, string $locale): ParameterValue
    {
        return $this->getParameterValueRepository()
            ->createQueryBuilder('pv')
            ->select('pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value and pv.locale = :locale')
            ->where('ppv.product = :product')
            ->andWhere('ppv.parameter = :parameter')
            ->setParameters([
                'locale' => $locale,
                'product' => $product,
                'parameter' => $parameter,
            ])
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function getVariantProductParameterValuesData(Product $product, string $locale, int $domainId): array
    {
        return $this->getProductParameterValueRepository()
            ->createQueryBuilder('ppv')
            ->join('ppv.product', 'p', Join::WITH, 'p.mainVariant = :product AND p.calculatedSellingDenied = FALSE')
            ->join('ppv.value', 'pv', Join::WITH, 'pv.locale = :locale')
            ->join(ProductVisibility::class, 'pvis', Join::WITH, 'p = pvis.product AND pvis.visible = TRUE AND pvis.domainId = :domainId')
            ->where('ppv.parameter IN (:variantParameters)')
            ->setParameters([
                'product' => $product,
                'variantParameters' => $product->getVariantParameters(),
                'locale' => $locale,
                'domainId' => $domainId,
            ])
            ->getQuery()
            ->getScalarResult();
    }
}

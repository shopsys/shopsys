<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Parameter\Exception\ParameterGroupNotFoundException;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProductSortedByName(\App\Model\Product\Product $product, string $locale)
 * @property \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
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
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private Localization $localization;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactoryInterface $parameterValueFactory
     * @param \App\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterValueFactoryInterface $parameterValueFactory,
        ParameterValueDataFactoryInterface $parameterValueDataFactory,
        Localization $localization
    ) {
        parent::__construct($entityManager, $parameterValueFactory, $parameterValueDataFactory);
        $this->localization = $localization;
    }

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
            'rgbHex' => $parameterValueData->rgbHex,
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
    public function getParameterValuesForVariantsByMainProductAndParameter(Product $product, Parameter $parameter, string $locale): array
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
    public function getParameterValueForVariantByProductVariantAndParameter(Product $product, Parameter $parameter, string $locale): ParameterValue
    {
        return $this->getParameterValueRepository()
            ->createQueryBuilder('pv')
            ->select('pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value and pv.locale = :locale')
            ->where('ppv.product = :product')
            ->andWhere('ppv.parameter = :parameter')
            ->addOrderBy('pv.id', 'ASC')
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
     * @param \App\Model\Product\Parameter\Parameter[] $parameters
     * @return array
     */
    public function getVariantProductParameterValuesData(Product $product, string $locale, int $domainId, array $parameters): array
    {
        return $this->getProductParameterValueRepository()
            ->createQueryBuilder('ppv')
            ->distinct()
            ->join('ppv.product', 'p', Join::WITH, 'p.mainVariant = :product AND p.calculatedSellingDenied = FALSE')
            ->join('ppv.value', 'pv', Join::WITH, 'pv.locale = :locale')
            ->join(ProductVisibility::class, 'pvis', Join::WITH, 'p = pvis.product AND pvis.visible = TRUE AND pvis.domainId = :domainId')
            ->where('ppv.parameter IN (:variantParameters)')
            ->addOrderBy('IDENTITY(ppv.parameter)', 'ASC')
            ->addOrderBy('IDENTITY(ppv.value)', 'ASC')
            ->setParameters([
                'product' => $product,
                'variantParameters' => $parameters,
                'locale' => $locale,
                'domainId' => $domainId,
            ])
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValuesViewData[]
     */
    public function getParameterValuesViewDataByProduct(Product $product, string $locale): array
    {
        $collation = $this->localization->getCollationByLocale($locale);
        $parameterValueRows = $this->getProductParameterValuesByProductSortedByNameQueryBuilder($product, $locale)
            ->select('pt.id AS parameterId, pt.name AS parameterName, pv.text AS valueText, 
                pg.akeneoCode AS parameterGroupCode, pgt.name AS parameterGroupName, put.name AS unitName')
            ->leftJoin('pg.translations', 'pgt', Join::WITH, 'pgt.locale = :locale')
            ->join('ppv.value', 'pv', Join::WITH, 'pv.locale = :locale')
            ->leftJoin('p.parameterUnit', 'pu')
            ->leftJoin('pu.translations', 'put', Join::WITH, 'put.locale = :locale')
            ->addOrderBy("COLLATE(pv.text, '" . $collation . "')", 'ASC')
            ->getQuery()
            ->getScalarResult();

        $parameterValuesViewDataByParameterId = [];
        foreach ($parameterValueRows as $parameterValueRow) {
            $parameterId = $parameterValueRow['parameterId'];
            if (array_key_exists($parameterId, $parameterValuesViewDataByParameterId) === false) {
                $parameterValuesViewDataByParameterId[$parameterId] = new ParameterValuesViewData(
                    $parameterValueRow['parameterName'],
                    $parameterValueRow['parameterGroupName'],
                    $parameterValueRow['parameterGroupCode'],
                    $parameterValueRow['unitName'],
                );
            }
            $parameterValuesViewDataByParameterId[$parameterId]->addParameterValueText($parameterValueRow['valueText']);
        }

        return $parameterValuesViewDataByParameterId;
    }

    /**
     * @param string $parameterValueText
     * @param string $locale
     * @return \App\Model\Product\Parameter\ParameterValue|null
     */
    public function findParameterValueByText(string $parameterValueText, string $locale): ?ParameterValue
    {
        $parameterValue = $this->getParameterValueRepository()->findOneBy([
            'text' => $parameterValueText,
            'locale' => $locale,
        ]);

        /** @var \App\Model\Product\Parameter\ParameterValue $parameterValue */
        return $parameterValue;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductQueryBuilder(BaseProduct $product)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('ppv')
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('ppv.value', 'pv')
            ->where('ppv.product = :product_id')
            ->orderBy('IDENTITY(p.group)')
            ->addOrderBy('p.id')
            ->addOrderBy('pv.locale')
            ->setParameter('product_id', $product->getId());

        return $queryBuilder;
    }

    /**
     * @param \App\Model\Product\Product[] $products
     * @param string $locale
     * @return array
     */
    public function getProductParameterValuesDataByProducts(array $products, string $locale): array
    {
        if (count($products) === 0) {
            return [];
        }

        return $this->em->createQueryBuilder()
            ->select('p.id as parameter_id, pv.id as parameter_value_id')
            ->distinct()
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale AND pt.name IS NOT NULL')
            ->join('ppv.value', 'pv', Join::WITH, 'pv.locale = :locale')
            ->where('ppv.product IN (:products)')
            ->setParameters([
                'products' => $products,
                'locale' => $locale,
            ])
            ->getQuery()
            ->execute();
    }

    /**
     * @return \App\Model\Product\Parameter\Parameter[]
     */
    public function getColorPickerParameters(): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(Parameter::class, 'p')
            ->where('p.parameterType = :parameter_type')
            ->setParameters([
                'parameter_type' => \App\Model\Product\Parameter\Parameter::PARAMETER_TYPE_COLOR,
            ]);

        return $queryBuilder->getQuery()->execute();
    }
}

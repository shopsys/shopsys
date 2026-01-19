<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterGroupNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomain;

class ParameterRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactory $parameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactory $parameterValueDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper $orderByCollationHelper
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ParameterValueFactory $parameterValueFactory,
        protected readonly ParameterValueDataFactory $parameterValueDataFactory,
        protected readonly OrderByCollationHelper $orderByCollationHelper,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getParameterRepository(): EntityRepository
    {
        return $this->em->getRepository(Parameter::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getParameterValueRepository(): EntityRepository
    {
        return $this->em->getRepository(ParameterValue::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getParameterGroupRepository(): EntityRepository
    {
        return $this->em->getRepository(ParameterGroup::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductParameterValueRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductParameterValue::class);
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public function findById(int $parameterId): ?Parameter
    {
        return $this->getParameterRepository()->find($parameterId);
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getById(int $parameterId): Parameter
    {
        $parameter = $this->findById($parameterId);

        if ($parameter === null) {
            $message = 'Parameter with ID ' . $parameterId . ' not found.';

            throw new ParameterNotFoundException($message);
        }

        return $parameter;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategory(Category $category, DomainConfig $domainConfig): array
    {
        $queryBuilder = $this->getParameterRepository()->createQueryBuilder('p')
            ->select('p')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'p = ppv.parameter')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $domainConfig->getLocale())
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('pt.name', $domainConfig->getLocale()))
            ->groupBy('p, pt');

        $this->applyCategorySeoConditions($queryBuilder, $category, $domainConfig->getId());

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     */
    protected function applyCategorySeoConditions(QueryBuilder $queryBuilder, Category $category, int $domainId): void
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
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getByUuid(string $uuid): Parameter
    {
        $parameter = $this->getParameterRepository()->findOneBy(['uuid' => $uuid]);

        if ($parameter === null) {
            throw new ParameterNotFoundException('Parameter with UUID ' . $uuid . ' does not exist.');
        }

        return $parameter;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueByUuid(string $uuid): ParameterValue
    {
        $parameterValue = $this->getParameterValueRepository()->findOneBy(['uuid' => $uuid]);

        if ($parameterValue === null) {
            throw new ParameterValueNotFoundException('ParameterValue with UUID ' . $uuid . ' does not exist.');
        }

        return $parameterValue;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAll(): array
    {
        return $this->getAllQueryBuilder()
            ->orderBy('p.orderingPriority', 'DESC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getAllWithTranslations(string $locale): array
    {
        return $this->getAllQueryBuilder()
            ->addSelect('pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('pt.name', $locale), 'asc')
            ->getQuery()
            ->execute();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Parameter::class, 'p');
    }

    /**
     * @param string $valueText
     * @param string|null $numericValue
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue|null
     */
    public function findParameterValueByValueTextNumericValueAndLocale(
        string $valueText,
        ?string $numericValue,
        string $locale,
    ): ?ParameterValue {
        return $this->getParameterValueRepository()->findOneBy([
            'text' => $valueText,
            'numericValue' => $numericValue,
            'locale' => $locale,
        ]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function findOrCreateParameterValueByParameterValueData(
        ParameterValueData $parameterValueData,
    ): ParameterValue {
        $parameterValue = $this->findParameterValueByValueTextNumericValueAndLocale(
            $parameterValueData->text,
            $parameterValueData->numericValue,
            $parameterValueData->locale,
        );

        if ($parameterValue === null) {
            $parameterValue = $this->parameterValueFactory->create($parameterValueData);
            $this->em->persist($parameterValue);
            // Doctrine's identity map is not cache.
            // We have to flush now, so that next findOneBy() finds new ParameterValue.
            $this->em->flush();
        }

        if ($parameterValue->getRgbHex() !== $parameterValueData->rgbHex) {
            $parameterValue->edit($parameterValueData);
            $this->em->flush();
        }

        return $parameterValue;
    }

    /**
     * @param string $valueText
     * @param string|null $numericValue
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueByValueTextNumericValueAndLocale(
        string $valueText,
        ?string $numericValue,
        string $locale,
    ): ParameterValue {
        $parameterValue = $this->findParameterValueByValueTextNumericValueAndLocale($valueText, $numericValue, $locale);

        if ($parameterValue === null) {
            throw new ParameterValueNotFoundException();
        }

        return $parameterValue;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductQueryBuilder(
        Product $product,
    ): QueryBuilder {
        return $this->em->createQueryBuilder()
            ->select('ppv')
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('ppv.value', 'pv')
            ->leftJoin('p.group', 'pg')
            ->where('ppv.product = :product_id')
            ->orderBy('CASE WHEN pg.position IS NULL THEN 1 ELSE 0 END', 'DESC')
            ->addOrderBy('pg.position', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->addOrderBy('pv.locale', 'ASC')
            ->addOrderBy('IDENTITY(p.group)')
            ->setParameter('product_id', $product->getId());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder(
        Product $product,
        string $locale,
    ): QueryBuilder {
        return $this->em->createQueryBuilder()
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
            ->orderBy('p.orderingPriority', 'DESC')
            ->addOrderBy('pg.position', 'ASC')
            ->addOrderBy('pt.name');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValuesByProduct(Product $product): array
    {
        $queryBuilder = $this->getProductParameterValuesByProductQueryBuilder($product);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValuesByProductSortedByOrderingPriorityAndName(
        Product $product,
        string $locale,
    ): array {
        $queryBuilder = $this->getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder($product, $locale);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string $locale
     * @return string[][]
     */
    public function getParameterValuesIndexedByProductIdAndParameterNameForProducts(
        array $products,
        string $locale,
    ): array {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('IDENTITY(ppv.product) as productId', 'pt.name', 'pv.text', 'uv.name as unit')
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('p.translations', 'pt')
            ->join('ppv.value', 'pv')
            ->leftJoin('p.unit', 'u')
            ->leftJoin('u.translations', 'uv', Join::WITH, 'uv.locale = :locale')
            ->where('ppv.product IN (:products)')
            ->andWhere('pv.locale = :locale')
            ->andWhere('pt.locale = :locale')
            ->setParameters([
                'products' => $products,
                'locale' => $locale,
            ]);

        $productIdsAndParameterNamesAndValues = $queryBuilder->getQuery()->execute(null, Query::HYDRATE_ARRAY);

        return $this->getParameterValuesIndexedByProductIdAndParameterName($productIdsAndParameterNamesAndValues);
    }

    /**
     * @param string[] $namesByLocale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByNames(array $namesByLocale): ?Parameter
    {
        $queryBuilder = $this->getParameterRepository()->createQueryBuilder('p');
        $index = 0;

        foreach ($namesByLocale as $locale => $name) {
            $alias = 'pt' . $index;
            $localeParameterName = 'locale' . $index;
            $nameParameterName = 'name' . $index;
            $queryBuilder->join(
                'p.translations',
                $alias,
                Join::WITH,
                'p = ' . $alias . '.translatable
                    AND ' . $alias . '.locale = :' . $localeParameterName . '
                    AND ' . $alias . '.name = :' . $nameParameterName,
            );
            $queryBuilder->setParameter($localeParameterName, $locale);
            $queryBuilder->setParameter($nameParameterName, $name);
            $index++;
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @param array $productIdsAndParameterNamesAndValues
     * @return string[][]
     */
    protected function getParameterValuesIndexedByProductIdAndParameterName(
        array $productIdsAndParameterNamesAndValues,
    ): array {
        $productParameterValuesIndexedByProductIdAndParameterName = [];

        foreach ($productIdsAndParameterNamesAndValues as $productIdAndParameterNameAndValue) {
            $parameterName = $productIdAndParameterNameAndValue['name'];
            $productId = $productIdAndParameterNameAndValue['productId'];
            $parameterValue = $productIdAndParameterNameAndValue['text'];

            if ($productIdAndParameterNameAndValue['unit'] !== '') {
                $parameterValue .= ' ' . $productIdAndParameterNameAndValue['unit'];
            }

            $productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] = $parameterValue;
        }

        return $productParameterValuesIndexedByProductIdAndParameterName;
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersByUuids(array $uuids): array
    {
        $parametersByUuid = [];
        $parameters = $this->getParameterRepository()->findBy(['uuid' => $uuids], ['orderingPriority' => 'DESC']);

        foreach ($parameters as $parameter) {
            $parametersByUuid[$parameter->getUuid()] = $parameter;
        }

        return $parametersByUuid;
    }

    /**
     * @param string[] $uuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByUuids(array $uuids): array
    {
        $parameterValuesByUuid = [];
        $parameterValues = $this->getParameterValueRepository()->findBy(['uuid' => $uuids]);

        foreach ($parameterValues as $parameterValue) {
            $parameterValuesByUuid[$parameterValue->getUuid()] = $parameterValue;
        }

        return $parameterValuesByUuid;
    }

    /**
     * @param int[] $parameterIds
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getVisibleParametersByIds(array $parameterIds, string $locale): array
    {
        $parametersQueryBuilder = $this->getParameterRepository()->createQueryBuilder('p')
            ->select('p, pt')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale')
            ->where('p.id IN (:parameterIds)')
            ->setParameter('parameterIds', $parameterIds)
            ->setParameter('locale', $locale)
            ->orderBy('p.orderingPriority', 'DESC')
            ->addOrderBy($this->orderByCollationHelper->createOrderByForLocale('pt.name', $locale), 'asc');

        return $parametersQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param int[] $parameterValueIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByIds(array $parameterValueIds): array
    {
        $parameterValues = $this->getParameterValueRepository()->createQueryBuilder('pv')
            ->where('pv.id IN (:parameterValueIds)')
            ->setParameter('parameterValueIds', $parameterValueIds)
            ->getQuery()->getResult();

        $parameterValuesIndexedById = [];

        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $parameterValue */
        foreach ($parameterValues as $parameterValue) {
            $parameterValuesIndexedById[$parameterValue->getId()] = $parameterValue;
        }

        return $parameterValuesIndexedById;
    }

    /**
     * @param string $locale
     * @param string $type
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderParameterValuesUsedByProductsByLocaleAndType(
        string $locale,
        string $type,
    ): QueryBuilder {
        return $this->getParameterValueRepository()->createQueryBuilder('pv')
            ->select('pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value and pv.locale = :locale')
            ->join(Parameter::class, 'p', Join::WITH, 'ppv.parameter = p and p.parameterType = :type')
            ->setParameter(':locale', $locale)
            ->setParameter(':type', $type)
            ->groupBy('pv')
            ->orderBy($this->orderByCollationHelper->createOrderByForLocale('pv.text', $locale));
    }

    /**
     * @param int $parameterValueId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueById(int $parameterValueId): ParameterValue
    {
        $parameterValue = $this->getParameterValueRepository()->find($parameterValueId);

        if ($parameterValue === null) {
            $message = 'ParameterValue with ID ' . $parameterValueId . ' not found.';

            throw new ParameterValueNotFoundException($message);
        }

        return $parameterValue;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesByParameter(Parameter $parameter): array
    {
        return $this->getParameterValueRepository()->createQueryBuilder('pv')
            ->select('pv')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'pv = ppv.value')
            ->join(Parameter::class, 'p', Join::WITH, 'ppv.parameter = p')
            ->where('ppv.parameter = :parameter')
            ->setParameter(':parameter', $parameter)
            ->groupBy('pv')
            ->orderBy('pv.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $oldParameterValue
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $newParameterValue
     */
    public function updateParameterValueInProductsByConversion(
        Parameter $parameter,
        ParameterValue $oldParameterValue,
        ParameterValue $newParameterValue,
    ): void {
        $this->getParameterValueRepository()->createQueryBuilder('pv')
            ->update(ProductParameterValue::class, 'ppv')
            ->set('ppv.value', ':newParameterValue')
            ->where('ppv.parameter = :parameter')
            ->andWhere('ppv.value = :oldParameterValue')
            ->setParameter(':parameter', $parameter)
            ->setParameter(':oldParameterValue', $oldParameterValue)
            ->setParameter(':newParameterValue', $newParameterValue)
            ->getQuery()
            ->execute();
    }

    /**
     * @return int
     */
    public function getCountOfSliderParametersWithoutTheirsNumericValueFilled(): int
    {
        try {
            return $this->getSliderParametersWithoutTheirsNumericValueFilledQueryBuilder()
                ->select('COUNT(DISTINCT ppv.value)')
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException) {
            return 0;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getSliderParametersWithoutTheirsNumericValueFilled(): array
    {
        return $this->getSliderParametersWithoutTheirsNumericValueFilledQueryBuilder()
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getSliderParametersWithoutTheirsNumericValueFilledQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('DISTINCT p')
            ->from(Parameter::class, 'p')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'ppv.parameter = p')
            ->join('ppv.value', 'pv')
            ->where('pv.numericValue IS NULL')
            ->andWhere('p.parameterType = :type')
            ->setParameter('type', Parameter::PARAMETER_TYPE_SLIDER);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return int
     */
    public function getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder(Parameter $parameter): int
    {
        try {
            return $this->em->createQueryBuilder()
                ->select('COUNT(DISTINCT ppv.value)')
                ->from(Parameter::class, 'p')
                ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'ppv.parameter = p')
                ->join('ppv.value', 'pv')
                ->where('pv.numericValue IS NULL')
                ->andWhere('p = :parameter')
                ->setParameter('parameter', $parameter)
                ->getQuery()
                ->getSingleScalarResult();
        } catch (NoResultException) {
            return 0;
        }
    }

    /**
     * @param string $name
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null $excludeParameter
     * @return bool
     */
    public function existsParameterByName(string $name, string $locale, ?Parameter $excludeParameter = null): bool
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('count(p)')
            ->from(Parameter::class, 'p')
            ->join('p.translations', 'pt')
            ->where('pt.name = :name')
            ->andWhere('pt.locale = :locale')
            ->setParameter('name', $name)
            ->setParameter('locale', $locale);

        if ($excludeParameter !== null) {
            $queryBuilder
                ->andWhere('p != :excludeParameter')
                ->setParameter('excludeParameter', $excludeParameter);
        }

        return $queryBuilder
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategoryWithoutSlider(Category $category, int $domainId): array
    {
        $queryBuilder = $this->getParameterRepository()->createQueryBuilder('p')
            ->select('p')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'p = ppv.parameter')
            ->where('p.parameterType != :parameterType')
            ->setParameter('parameterType', Parameter::PARAMETER_TYPE_SLIDER)
            ->orderBy('p.orderingPriority', 'DESC');

        $this->applyCategorySeoConditions($queryBuilder, $category, $domainId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @param int $domainId
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[]
     */
    public function getParameterValuesUsedByProductsInCategoryByParameter(
        Category $category,
        Parameter $parameter,
        int $domainId,
        string $locale,
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
     * @param string[] $parameterUuids
     * @return array<string, int>
     */
    public function getParameterIdsIndexedByUuids(array $parameterUuids): array
    {
        return $this->getIdsIndexedByUuids($parameterUuids, Parameter::class);
    }

    /**
     * @param string[] $parameterValueUuids
     * @return array<string, int>
     */
    public function getParameterValueIdsIndexedByUuids(array $parameterValueUuids): array
    {
        return $this->getIdsIndexedByUuids($parameterValueUuids, ParameterValue::class);
    }

    /**
     * @param string $text
     * @param string $locale
     * @return int
     */
    public function getParameterValueIdByText(string $text, string $locale): int
    {
        return $this->em->createQueryBuilder()
            ->select('pv.id')
            ->from(ParameterValue::class, 'pv')
            ->where('pv.text = :text')
            ->andWhere('pv.locale = :locale')
            ->andWhere('pv.numericValue IS NULL')
            ->setParameters([
                'text' => $text,
                'locale' => $locale,
            ])
            ->getQuery()->getSingleScalarResult();
    }

    /**
     * @param string[] $uuids
     * @param string $entityName
     * @return array<string, int>
     */
    protected function getIdsIndexedByUuids(array $uuids, string $entityName): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p.id, p.uuid')
            ->from($entityName, 'p')
            ->where('p.uuid IN (:uuids)')
            ->setParameter('uuids', $uuids);

        if ($entityName === Parameter::class) {
            $queryBuilder->orderBy('p.orderingPriority', 'DESC');
        }

        $idsIndexedByUuids = [];

        foreach ($queryBuilder->getQuery()->getArrayResult() as $idAndUuid) {
            $idsIndexedByUuids[$idAndUuid['uuid']] = $idAndUuid['id'];
        }

        return $idsIndexedByUuids;
    }

    /**
     * @param int $parameterGroupId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup[]
     */
    public function getAllParameterGroups(): array
    {
        return $this->em->createQueryBuilder()
            ->select('pg')
            ->from(ParameterGroup::class, 'pg')
            ->orderBy('pg.position', 'ASC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getOrderedParameterGroupsQueryBuilder(string $locale): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pg, pgt')
            ->from(ParameterGroup::class, 'pg')
            ->join('pg.translations', 'pgt')
            ->where('pgt.locale = :locale')
            ->setParameter('locale', $locale)
            ->orderBy('pg.position', 'ASC');
    }

    /**
     * @param string $name
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup|null $excludeParameterGroup
     * @return bool
     */
    public function existsParameterGroupByName(
        string $name,
        string $locale,
        ?ParameterGroup $excludeParameterGroup = null,
    ): bool {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('count(pg)')
            ->from(ParameterGroup::class, 'pg')
            ->join('pg.translations', 'pgt')
            ->where('pgt.name = :name')
            ->andWhere('pgt.locale = :locale')
            ->setParameter('name', $name)
            ->setParameter('locale', $locale);

        if ($excludeParameterGroup !== null) {
            $queryBuilder
                ->andWhere('pg != :excludeParameterGroup')
                ->setParameter('excludeParameterGroup', $excludeParameterGroup);
        }

        return $queryBuilder
                ->getQuery()
                ->getSingleScalarResult() > 0;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string $locale
     * @return array
     */
    public function getProductParameterValuesDataByProducts(array $products, string $locale): array
    {
        if (count($products) === 0) {
            return [];
        }

        $collatedParameterName = $this->orderByCollationHelper->createOrderByForLocale('pt.name', $locale);

        return $this->em->createQueryBuilder()
            ->select(
                'p.id as parameter_id,
                p.orderingPriority as ordering_priority,
                p.parameterType as parameter_type,
                pv.id as parameter_value_id,
                p.uuid as parameter_uuid,
                pt.name as parameter_name,
                pv.uuid as parameter_value_uuid,
                pv.text as parameter_value_text,
                pv.numericValue as parameter_value_numeric_value,
                pv.rgbHex as parameter_value_rgbHex,
                pgt.name as parameter_group,
                pg.position as group_position,
                put.name as parameter_unit,
                ' . $collatedParameterName,
            )
            ->distinct()
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('p.translations', 'pt', Join::WITH, 'pt.locale = :locale AND pt.name IS NOT NULL')
            ->leftjoin('p.group', 'pg')
            ->leftJoin('pg.translations', 'pgt', Join::WITH, 'pgt.locale = :locale AND pgt.name IS NOT NULL')
            ->leftJoin('p.unit', 'pu')
            ->leftJoin('pu.translations', 'put', Join::WITH, 'put.locale = :locale AND put.name IS NOT NULL')
            ->join('ppv.value', 'pv', Join::WITH, 'pv.locale = :locale')
            ->where('ppv.product IN (:products)')
            ->orderBy('group_position', 'ASC')
            ->addOrderBy('ordering_priority', 'DESC')
            ->addOrderBy($collatedParameterName, 'ASC')
            ->setParameters([
                'products' => $products,
                'locale' => $locale,
            ])
            ->getQuery()
            ->execute();
    }
}

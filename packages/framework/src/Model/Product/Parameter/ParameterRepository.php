<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Parameter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\OrderByCollationHelper;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ParameterRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueFactoryInterface $parameterValueFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueDataFactoryInterface $parameterValueDataFactory
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        protected readonly ParameterValueFactoryInterface $parameterValueFactory,
        protected readonly ParameterValueDataFactoryInterface $parameterValueDataFactory,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getParameterRepository()
    {
        return $this->em->getRepository(Parameter::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getParameterValueRepository()
    {
        return $this->em->getRepository(ParameterValue::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductParameterValueRepository()
    {
        return $this->em->getRepository(ProductParameterValue::class);
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public function findById($parameterId)
    {
        return $this->getParameterRepository()->find($parameterId);
    }

    /**
     * @param int $parameterId
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter
     */
    public function getById($parameterId)
    {
        $parameter = $this->findById($parameterId);

        if ($parameter === null) {
            $message = 'Parameter with ID ' . $parameterId . ' not found.';

            throw new ParameterNotFoundException($message);
        }

        return $parameter;
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
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Parameter::class, 'p')
            ->join('p.translations', 'pt')
            ->orderBy('p.orderingPriority', 'DESC')
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $valueText
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function findOrCreateParameterValueByValueTextAndLocale($valueText, $locale)
    {
        $parameterValue = $this->getParameterValueRepository()->findOneBy([
            'text' => $valueText,
            'locale' => $locale,
        ]);

        if ($parameterValue === null) {
            $parameterValueData = $this->parameterValueDataFactory->create();
            $parameterValueData->text = $valueText;
            $parameterValueData->locale = $locale;
            $parameterValue = $this->parameterValueFactory->create($parameterValueData);
            $this->em->persist($parameterValue);
            // Doctrine's identity map is not cache.
            // We have to flush now, so that next findOneBy() finds new ParameterValue.
            $this->em->flush();
        }

        return $parameterValue;
    }

    /**
     * @param string $valueText
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function getParameterValueByValueTextAndLocale(string $valueText, string $locale): ParameterValue
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue|null $parameterValue */
        $parameterValue = $this->getParameterValueRepository()->findOneBy([
            'text' => $valueText,
            'locale' => $locale,
        ]);

        if ($parameterValue === null) {
            throw new ParameterValueNotFoundException();
        }

        return $parameterValue;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductQueryBuilder(Product $product)
    {
        return $this->em->createQueryBuilder()
            ->select('ppv')
            ->from(ProductParameterValue::class, 'ppv')
            ->where('ppv.product = :product_id')
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
            ->where('ppv.product = :product_id')
            ->andWhere('pt.locale = :locale')
            ->setParameters([
                'product_id' => $product->getId(),
                'locale' => $locale,
            ])
            ->orderBy('p.orderingPriority', 'DESC')
            ->addOrderBy('pt.name');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValuesByProduct(Product $product)
    {
        $queryBuilder = $this->getProductParameterValuesByProductQueryBuilder($product);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValuesByProductSortedByOrderingPriorityAndName(Product $product, $locale)
    {
        $queryBuilder = $this->getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder($product, $locale);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string $locale
     * @return string[][]
     */
    public function getParameterValuesIndexedByProductIdAndParameterNameForProducts(array $products, $locale)
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('IDENTITY(ppv.product) as productId', 'pt.name', 'pv.text')
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('p.translations', 'pt')
            ->join('ppv.value', 'pv')
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[]
     */
    public function getProductParameterValuesByParameter(Parameter $parameter)
    {
        return $this->getProductParameterValueRepository()->findBy([
            'parameter' => $parameter,
        ]);
    }

    /**
     * @param string[] $namesByLocale
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter|null
     */
    public function findParameterByNames(array $namesByLocale)
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
    ) {
        $productParameterValuesIndexedByProductIdAndParameterName = [];

        foreach ($productIdsAndParameterNamesAndValues as $productIdAndParameterNameAndValue) {
            $parameterName = $productIdAndParameterNameAndValue['name'];
            $productId = $productIdAndParameterNameAndValue['productId'];
            $parameterValue = $productIdAndParameterNameAndValue['text'];
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
            ->addOrderBy(OrderByCollationHelper::createOrderByForLocale('pt.name', $locale), 'asc');

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
            ->orderBy(OrderByCollationHelper::createOrderByForLocale('pv.text', $locale));
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsByParameterValues(array $parameterValues): array
    {
        return $this->em->createQueryBuilder()
            ->select('p')
            ->from(Product::class, 'p')
            ->join(ProductParameterValue::class, 'ppv', Join::WITH, 'ppv.product = p')
            ->where('ppv.value IN(:parameterValues)')
            ->setParameter('parameterValues', $parameterValues)
            ->getQuery()
            ->execute();
    }
}

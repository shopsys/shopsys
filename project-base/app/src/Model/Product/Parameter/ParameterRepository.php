<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Parameter\Exception\ParameterGroupNotFoundException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;

/**
 * @method \App\Model\Product\Parameter\Parameter|null findById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter getById(int $parameterId)
 * @method \App\Model\Product\Parameter\Parameter[] getParametersUsedByProductsInCategory(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method applyCategorySeoConditions(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Category\Category $category, int $domainId)
 * @method \App\Model\Product\Parameter\Parameter getByUuid(string $uuid)
 * @method \App\Model\Product\Parameter\Parameter[] getAll()
 * @method \App\Model\Product\Parameter\Parameter[] getAllWithTranslations(string $locale)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProductSortedByOrderingPriorityAndName(\App\Model\Product\Product $product, string $locale)
 * @method string[][] getParameterValuesIndexedByProductIdAndParameterNameForProducts(\App\Model\Product\Product[] $products, string $locale)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method \App\Model\Product\Parameter\Parameter|null findParameterByNames(string[] $namesByLocale)
 * @method \App\Model\Product\Parameter\Parameter[] getParametersByUuids(string[] $uuids)
 * @method \App\Model\Product\Parameter\Parameter[] getVisibleParametersByIds(int[] $parameterIds, string $locale)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] getParameterValuesByParameter(\App\Model\Product\Parameter\Parameter $parameter)
 * @method updateParameterValueInProductsByConversion(\App\Model\Product\Parameter\Parameter $parameter, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $oldParameterValue, \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue $newParameterValue)
 * @method \App\Model\Product\Parameter\Parameter[] getSliderParametersWithoutTheirsNumericValueFilled()
 * @method int getCountOfParameterValuesWithoutTheirsNumericValueFilledQueryBuilder(\App\Model\Product\Parameter\Parameter $parameter)
 * @method \App\Model\Product\Product[] getProductsByParameterValues(\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues)
 * @method bool existsParameterByName(string $name, string $locale, \App\Model\Product\Parameter\Parameter|null $excludeParameter = null)
 * @method \App\Model\Product\Parameter\Parameter[] getParametersUsedByProductsInCategoryWithoutSlider(\App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] getParameterValuesUsedByProductsInCategoryByParameter(\App\Model\Category\Category $category, \App\Model\Product\Parameter\Parameter $parameter, int $domainId, string $locale)
 */
class ParameterRepository extends BaseParameterRepository
{
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
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getParameterGroupRepository(): EntityRepository
    {
        return $this->em->getRepository(ParameterGroup::class);
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
     * @param \App\Model\Product\Product $product
     * @param string $locale
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder(
        BaseProduct $product,
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
            ->addOrderBy('pg.orderingPriority', 'DESC')
            ->addOrderBy('pt.name');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData $parameterValueData
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue
     */
    public function findOrCreateParameterValueByParameterValueData(
        ParameterValueData $parameterValueData,
    ): ParameterValue {
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

        if ($parameterValue->getRgbHex() !== $parameterValueData->rgbHex) {
            $parameterValue->edit($parameterValueData);
            $this->em->flush();
        }

        return $parameterValue;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getProductParameterValuesByProductQueryBuilder(BaseProduct $product)
    {
        return $this->em->createQueryBuilder()
            ->select('ppv')
            ->from(ProductParameterValue::class, 'ppv')
            ->join('ppv.parameter', 'p')
            ->join('ppv.value', 'pv')
            ->where('ppv.product = :product_id')
            ->orderBy('IDENTITY(p.group)')
            ->addOrderBy('p.id')
            ->addOrderBy('pv.locale')
            ->setParameter('product_id', $product->getId());
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
                pgt.name as parameter_group,
                put.name as parameter_unit',
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
            ->orderBy('ordering_priority', 'DESC')
            ->addOrderBy('parameter_name')
            ->setParameters([
                'products' => $products,
                'locale' => $locale,
            ])
            ->getQuery()
            ->execute();
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

            if ($productIdAndParameterNameAndValue['unit'] !== '') {
                $parameterValue .= ' ' . $productIdAndParameterNameAndValue['unit'];
            }

            if ($productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] ?? false) {
                $productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] .= '/' . $parameterValue;
            } else {
                $productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] = $parameterValue;
            }
        }

        return $productParameterValuesIndexedByProductIdAndParameterName;
    }

    /**
     * @return \App\Model\Product\Parameter\ParameterGroup[]
     */
    public function getAllParameterGroups(): array
    {
        return $this->em->createQueryBuilder()
            ->select('pg')
            ->from(ParameterGroup::class, 'pg')
            ->orderBy('pg.orderingPriority', 'ASC')
            ->getQuery()
            ->execute();
    }
}

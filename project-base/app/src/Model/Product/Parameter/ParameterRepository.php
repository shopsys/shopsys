<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use App\Model\Product\Parameter\Exception\ParameterValueNotFoundException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValueData;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] getParametersUsedByProductsInCategory(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method applyCategorySeoConditions(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProductSortedByOrderingPriorityAndName(\App\Model\Product\Product $product, string $locale)
 * @method string[][] getParameterValuesIndexedByProductIdAndParameterNameForProducts(\App\Model\Product\Product[] $products, string $locale)
 * @method \App\Model\Product\Product[] getProductsByParameterValues(\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues)
 * @method \Doctrine\ORM\QueryBuilder getProductParameterValuesByProductQueryBuilder(\App\Model\Product\Product $product)
 */
class ParameterRepository extends BaseParameterRepository
{
    /**
     * @param \App\Model\Category\Category $category
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
                pg.position as group_position,
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
            ->orderBy('group_position', 'ASC')
            ->addOrderBy('ordering_priority', 'DESC')
            ->addOrderBy('parameter_name', 'ASC')
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
}

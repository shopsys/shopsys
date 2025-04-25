<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Doctrine\ORM\Query\Expr\Join;
use Override;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue;

/**
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] getParametersUsedByProductsInCategory(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method applyCategorySeoConditions(\Doctrine\ORM\QueryBuilder $queryBuilder, \App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ProductParameterValue[] getProductParameterValuesByProductSortedByOrderingPriorityAndName(\App\Model\Product\Product $product, string $locale)
 * @method string[][] getParameterValuesIndexedByProductIdAndParameterNameForProducts(\App\Model\Product\Product[] $products, string $locale)
 * @method \App\Model\Product\Product[] getProductsByParameterValues(\Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues)
 * @method \Doctrine\ORM\QueryBuilder getProductParameterValuesByProductQueryBuilder(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[] getParametersUsedByProductsInCategoryWithoutSlider(\App\Model\Category\Category $category, int $domainId)
 * @method \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] getParameterValuesUsedByProductsInCategoryByParameter(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter, int $domainId, string $locale)
 * @method \Doctrine\ORM\QueryBuilder getProductParameterValuesByProductSortedByOrderingPriorityAndNameQueryBuilder(\App\Model\Product\Product $product, string $locale)
 */
class ParameterRepository extends BaseParameterRepository
{
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
    #[Override]
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

            if ($productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] ?? false) {
                $productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] .= '/' . $parameterValue;
            } else {
                $productParameterValuesIndexedByProductIdAndParameterName[$productId][$parameterName] = $parameterValue;
            }
        }

        return $productParameterValuesIndexedByProductIdAndParameterName;
    }
}

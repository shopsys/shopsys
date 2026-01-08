<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository as BaseParameterRepository;

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
 * @method array getProductParameterValuesDataByProducts(\App\Model\Product\Product[] $products, string $locale)
 */
class ParameterRepository extends BaseParameterRepository
{
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

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterGroup;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class AffectedProductsFacade
{
    public function __construct(
        protected readonly AffectedProductsRepository $affectedProductsRepository,
    ) {
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithBrand(Brand $brand): array
    {
        return $this->affectedProductsRepository->getProductIdsWithBrand($brand);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithCategory(Category $category): array
    {
        return $this->affectedProductsRepository->getProductIdsWithCategory($category);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithFlag(Flag $flag): array
    {
        return $this->affectedProductsRepository->getProductIdsWithFlag($flag);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithParameter(Parameter $parameter): array
    {
        return $this->affectedProductsRepository->getProductIdsWithParameter($parameter);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithParameterGroup(ParameterGroup $parameterGroup): array
    {
        return $this->affectedProductsRepository->getProductIdsWithParameterGroup($parameterGroup);
    }

    /**
     * @return int[]
     */
    public function getProductIdsWithUnit(Unit $unit): iterable
    {
        return $this->affectedProductsRepository->getProductIdsWithUnit($unit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue[] $parameterValues
     * @return int[]
     */
    public function getProductIdsWithParameterValues(array $parameterValues): array
    {
        return $this->affectedProductsRepository->getProductIdsWithParameterValues($parameterValues);
    }
}

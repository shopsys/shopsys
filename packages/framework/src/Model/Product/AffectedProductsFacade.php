<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Availability\Availability;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;

class AffectedProductsFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\AffectedProductsRepository $affectedProductsRepository
     */
    public function __construct(
        protected readonly AffectedProductsRepository $affectedProductsRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\Availability $availability
     * @return int[]
     */
    public function getProductIdsWithAvailability(Availability $availability): array
    {
        return $this->affectedProductsRepository->getProductIdsWithAvailability($availability);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return int[]
     */
    public function getProductIdsWithBrand(Brand $brand): array
    {
        return $this->affectedProductsRepository->getProductIdsWithBrand($brand);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return int[]
     */
    public function getProductIdsWithCategory(Category $category): array
    {
        return $this->affectedProductsRepository->getProductIdsWithCategory($category);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
     * @return int[]
     */
    public function getProductIdsWithFlag(Flag $flag): array
    {
        return $this->affectedProductsRepository->getProductIdsWithFlag($flag);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter $parameter
     * @return int[]
     */
    public function getProductIdsWithParameter(Parameter $parameter): array
    {
        return $this->affectedProductsRepository->getProductIdsWithParameter($parameter);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Unit\Unit $unit
     * @return int[]
     */
    public function getProductIdsWithUnit(Unit $unit): iterable
    {
        return $this->affectedProductsRepository->getProductIdsWithUnit($unit);
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\CategorySeo;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterRepository;

class CategorySeoFacade
{
    public function __construct(
        protected readonly ParameterRepository $parameterRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter[]
     */
    public function getParametersUsedByProductsInCategoryWithoutSlider(Category $category, int $domainId): array
    {
        return $this->parameterRepository->getParametersUsedByProductsInCategoryWithoutSlider($category, $domainId);
    }
}

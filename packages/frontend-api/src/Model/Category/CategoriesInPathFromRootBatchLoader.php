<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade as FrameworkCategoryFacade;

class CategoriesInPathFromRootBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly FrameworkCategoryFacade $categoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    public function loadByCategories(array $categories): Promise
    {
        $categoriesInPathsIndexedByCategoryId = $this->categoryFacade->getVisibleCategoriesInPathsFromRootOnDomainIndexedByCategoryId(
            $categories,
            $this->domain->getId(),
        );

        $categoriesInPaths = [];

        foreach ($categories as $category) {
            $categoriesInPaths[] = $categoriesInPathsIndexedByCategoryId[$category->getId()];
        }

        return $this->promiseAdapter->all($categoriesInPaths);
    }
}

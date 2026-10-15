<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductListFriendlyUrlDataProvider;

class CategoryBreadcrumbBatchLoader
{
    public function __construct(
        protected readonly DataLoaderInterface $categoriesInPathFromRootBatchLoader,
        protected readonly CategoryBreadcrumbLinksFactory $categoryBreadcrumbLinksFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    public function loadBreadcrumbByCategories(array $categories): Promise
    {
        return $this->categoriesInPathFromRootBatchLoader
            ->loadMany($categories)
            ->then(fn (array $categoriesInPaths): array => $this->categoryBreadcrumbLinksFactory->createByPaths(
                $categoriesInPaths,
                ProductListFriendlyUrlDataProvider::ROUTE_NAME,
            ));
    }
}

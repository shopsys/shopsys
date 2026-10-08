<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Category;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrontendApiBundle\Model\Breadcrumb\BreadcrumbLinksFactory;

class CategoryBreadcrumbBatchLoader
{
    protected const string ROUTE_NAME = 'front_product_list';

    public function __construct(
        protected readonly DataLoaderInterface $categoriesInPathFromRootBatchLoader,
        protected readonly BreadcrumbLinksFactory $breadcrumbLinksFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category[] $categories
     */
    public function loadBreadcrumbByCategories(array $categories): Promise
    {
        return $this->categoriesInPathFromRootBatchLoader
            ->loadMany($categories)
            ->then(fn (array $categoriesInPaths): array => $this->breadcrumbLinksFactory->createByPaths(
                $categoriesInPaths,
                static::ROUTE_NAME,
            ));
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDetailFriendlyUrlDataProvider;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Category\CategoryBreadcrumbLinksFactory;

class BlogCategoryBreadcrumbBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly CategoryBreadcrumbLinksFactory $categoryBreadcrumbLinksFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     */
    public function loadBreadcrumbByBlogCategories(array $blogCategories): Promise
    {
        $blogCategoriesInPathsIndexedByBlogCategoryId = $this->blogCategoryFacade->getVisibleBlogCategoriesInPathsFromRootOnDomainIndexedByBlogCategoryId(
            $blogCategories,
            $this->domain->getId(),
        );

        $blogCategoriesInPaths = [];

        foreach ($blogCategories as $blogCategory) {
            $blogCategoriesInPaths[] = $blogCategoriesInPathsIndexedByBlogCategoryId[$blogCategory->getId()];
        }

        return $this->promiseAdapter->all($this->categoryBreadcrumbLinksFactory->createByPaths(
            $blogCategoriesInPaths,
            BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME,
        ));
    }
}

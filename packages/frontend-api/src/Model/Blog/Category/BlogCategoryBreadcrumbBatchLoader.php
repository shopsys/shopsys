<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Breadcrumb\BreadcrumbLinksFactory;

class BlogCategoryBreadcrumbBatchLoader
{
    protected const string ROUTE_NAME = 'front_blogcategory_detail';

    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly BreadcrumbLinksFactory $breadcrumbLinksFactory,
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
            $this->domain->getLocale(),
        );

        return $this->promiseAdapter->all($this->breadcrumbLinksFactory->createByPaths(
            array_values($blogCategoriesInPathsIndexedByBlogCategoryId),
            static::ROUTE_NAME,
        ));
    }
}

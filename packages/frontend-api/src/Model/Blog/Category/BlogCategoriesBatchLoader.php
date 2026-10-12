<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;

class BlogCategoriesBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     */
    public function loadChildrenByBlogCategories(array $blogCategories): Promise
    {
        $childrenIndexedByParentId = $this->blogCategoryFacade->getAllVisibleChildrenByBlogCategoriesAndDomainIdIndexedByParentId(
            $blogCategories,
            $this->domain->getId(),
        );

        $childrenByBlogCategory = [];

        foreach ($blogCategories as $blogCategory) {
            $childrenByBlogCategory[] = $childrenIndexedByParentId[$blogCategory->getId()];
        }

        return $this->promiseAdapter->all($childrenByBlogCategory);
    }
}

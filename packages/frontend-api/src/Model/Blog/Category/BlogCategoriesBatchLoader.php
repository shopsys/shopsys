<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\Exception\BlogCategoryNotFoundException;

class BlogCategoriesBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int[] $blogCategoryIds
     */
    public function loadByIds(array $blogCategoryIds): Promise
    {
        $blogCategoriesIndexedById = $this->blogCategoryFacade->getBlogCategoriesWithTranslationIndexedById(
            $blogCategoryIds,
            $this->domain->getLocale(),
        );
        $blogCategories = [];

        foreach ($blogCategoryIds as $blogCategoryId) {
            if (!isset($blogCategoriesIndexedById[$blogCategoryId])) {
                throw new BlogCategoryNotFoundException('BlogCategory with ID ' . $blogCategoryId . ' not found.');
            }

            $blogCategories[] = $blogCategoriesIndexedById[$blogCategoryId];
        }

        return $this->promiseAdapter->all($blogCategories);
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

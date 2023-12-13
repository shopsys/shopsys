<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

class BlogCategoryWithPreloadedChildrenFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    public function createBlogCategoriesWithPreloadedChildren(array $blogCategories): array
    {
        $firstLevelBlogCategories = $this->getFirstLevelBlogCategories($blogCategories);
        $blogCategoriesByParentId = $this->getBlogCategoriesIndexedByParentId($blogCategories);

        $blogCategoriesWithPreloadedChildren = [];

        foreach ($firstLevelBlogCategories as $firstLevelBlogCategory) {
            $blogCategoriesWithPreloadedChildren[] = $this->createInstance($firstLevelBlogCategory, $blogCategoriesByParentId);
        }

        return $blogCategoriesWithPreloadedChildren;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogCategory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[][] $blogCategoriesByParentId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    protected function getBlogCategoriesWithPreloadedChildren(
        BlogCategory $blogCategory,
        array $blogCategoriesByParentId,
    ): array {
        if (!array_key_exists($blogCategory->getId(), $blogCategoriesByParentId)) {
            return [];
        }

        $childBlogCategoriesWithPreloadedChildren = [];

        foreach ($blogCategoriesByParentId[$blogCategory->getId()] as $blogChildCategory) {
            $childBlogCategoriesWithPreloadedChildren[] = $this->createInstance($blogChildCategory, $blogCategoriesByParentId);
        }

        return $childBlogCategoriesWithPreloadedChildren;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[]
     */
    protected function getFirstLevelBlogCategories(array $blogCategories): array
    {
        $firstLevelBlogCategories = [];

        foreach ($blogCategories as $blogCategory) {
            if ($blogCategory->getLevel() === 1) {
                $firstLevelBlogCategories[] = $blogCategory;
            }
        }

        return $firstLevelBlogCategories;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[][]
     */
    protected function getBlogCategoriesIndexedByParentId(array $blogCategories): array
    {
        $blogCategoriesIndexedByParentId = [];

        foreach ($blogCategories as $blogCategory) {
            if ($blogCategory->getParent() === null) {
                continue;
            }

            $parentId = $blogCategory->getParent()->getId();

            if (!isset($blogCategoriesIndexedByParentId[$parentId])) {
                $blogCategoriesIndexedByParentId[$parentId] = [];
            }

            $blogCategoriesIndexedByParentId[$parentId][] = $blogCategory;
        }

        return $blogCategoriesIndexedByParentId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory $blogChildCategory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[][] $blogCategoriesByParentId
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildren
     */
    protected function createInstance(
        BlogCategory $blogChildCategory,
        array $blogCategoriesByParentId,
    ): BlogCategoryWithPreloadedChildren {
        return new BlogCategoryWithPreloadedChildren(
            $blogChildCategory,
            $this->getBlogCategoriesWithPreloadedChildren($blogChildCategory, $blogCategoriesByParentId),
        );
    }
}

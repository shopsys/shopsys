<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

class BlogCategoryWithPreloadedChildrenFactory
{
    /**
     * @param \App\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \App\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    public function createBlogCategoriesWithPreloadedChildren(array $blogCategories): array
    {
        $firstLevelBlogCategories = $this->getFirstLevelBlogCategories($blogCategories);
        $blogCategoriesByParentId = $this->getBlogCategoriesIndexedByParentId($blogCategories);

        $blogCategoriesWithPreloadedChildren = [];
        foreach ($firstLevelBlogCategories as $firstLevelBlogCategory) {
            $blogCategoriesWithPreloadedChildren[] = new BlogCategoryWithPreloadedChildren(
                $firstLevelBlogCategory,
                $this->getBlogCategoriesWithPreloadedChildren($firstLevelBlogCategory, $blogCategoriesByParentId)
            );
        }

        return $blogCategoriesWithPreloadedChildren;
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory $blogCategory
     * @param \App\Model\Blog\Category\BlogCategory[][] $blogCategoriesByParentId
     * @return \App\Model\Blog\Category\BlogCategoryWithPreloadedChildren[]
     */
    private function getBlogCategoriesWithPreloadedChildren(BlogCategory $blogCategory, array $blogCategoriesByParentId): array
    {
        if (!array_key_exists($blogCategory->getId(), $blogCategoriesByParentId)) {
            return [];
        }

        $childBlogCategoriesWithPreloadedChildren = [];

        foreach ($blogCategoriesByParentId[$blogCategory->getId()] as $blogChildCategory) {
            $childBlogCategoriesWithPreloadedChildren[] = new BlogCategoryWithPreloadedChildren(
                $blogChildCategory,
                $this->getBlogCategoriesWithPreloadedChildren($blogChildCategory, $blogCategoriesByParentId)
            );
        }

        return $childBlogCategoriesWithPreloadedChildren;
    }

    /**
     * @param \App\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \App\Model\Blog\Category\BlogCategory[]
     */
    protected function getFirstLevelBlogCategories(array $blogCategories)
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
     * @param \App\Model\Blog\Category\BlogCategory[] $blogCategories
     * @return \App\Model\Blog\Category\BlogCategory[][]
     */
    protected function getBlogCategoriesIndexedByParentId(array $blogCategories)
    {
        $blogCategoriesIndexedByParentId = [];

        foreach ($blogCategories as $blogCategory) {
            $parentId = $blogCategory->getParent()->getId();

            if ($parentId !== null) {
                if (!isset($blogCategoriesIndexedByParentId[$parentId])) {
                    $blogCategoriesIndexedByParentId[$parentId] = [];
                }

                $blogCategoriesIndexedByParentId[$parentId][] = $blogCategory;
            }
        }

        return $blogCategoriesIndexedByParentId;
    }
}

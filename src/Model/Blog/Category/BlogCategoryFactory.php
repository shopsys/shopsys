<?php

declare(strict_types=1);

namespace App\Model\Blog\Category;

class BlogCategoryFactory
{
    /**
     * @param \App\Model\Blog\Category\BlogCategoryData $data
     * @param \App\Model\Blog\Category\BlogCategory|null $rootBlogCategory
     * @return \App\Model\Blog\Category\BlogCategory
     */
    public function create(BlogCategoryData $data, ?BlogCategory $rootBlogCategory): BlogCategory
    {
        $blogCategory = new BlogCategory($data);

        if ($rootBlogCategory !== null && $blogCategory->getParent() === null) {
            $blogCategory->setParent($rootBlogCategory);
        }

        return $blogCategory;
    }
}

<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class BlogCategoryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryData $data
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory|null $rootBlogCategory
     * @return \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory
     */
    public function create(BlogCategoryData $data, ?BlogCategory $rootBlogCategory): BlogCategory
    {
        $entityClass = $this->entityNameResolver->resolve(BlogCategory::class);
        $blogCategory = new $entityClass($data);

        if ($rootBlogCategory !== null && $blogCategory->getParent() === null) {
            $blogCategory->setParent($rootBlogCategory);
        }

        return $blogCategory;
    }
}
